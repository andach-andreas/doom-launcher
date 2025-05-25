<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Port extends Model
{
    protected $fillable = ['name', 'slug', 'github_url'];

    public function installs()
    {
        return $this->hasMany(Install::class);
    }

    public function downloadAndExtractLatest()
    {
        $install = $this->installs()
            ->whereNotNull('download_url')
            ->orderByDesc('version')
            ->first();

        if ($install) {
            $install->downloadAndExtract();
        }
    }

    protected function fetchGitHubReleases(): array
    {
        if (!$this->github_url) return [];

        $matches = [];
        preg_match('#github\.com/([^/]+/[^/]+)#', $this->github_url, $matches);
        if (!isset($matches[1])) return [];

        $apiUrl = "https://api.github.com/repos/{$matches[1]}/releases";

        return Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'LaravelApp'
        ])->get($apiUrl)->json() ?? [];
    }

    public function latestInstall(): Attribute
    {
        return Attribute::get(function () {
            return $this->installs
                ->filter(fn($i) => !empty($i->version))
                ->sort(fn($a, $b) => version_compare($b->version, $a->version))
                ->first();
        });
    }

    public function syncReleases()
    {
        $releases = $this->fetchGitHubReleases();

        foreach ($releases as $release) {
            $version = $release['tag_name'];
            $slug = "{$this->slug}-{$version}";

            if ($this->installs()->where('version', $version)->exists()) {
                continue;
            }

            $zipAsset = collect($release['assets'])->first(function ($a) {
                $name = strtolower($a['name']);
                return str_ends_with($name, '.zip') && (
                        str_contains($name, 'win') ||
                        str_contains($name, 'windows')
                    );
            });

            $downloadUrl = $zipAsset['browser_download_url'] ?? null;

            $this->installs()->create([
                'version'       => $version,
                'slug'          => $slug,
                'download_url'  => $downloadUrl,
                'available'     => !is_null($downloadUrl),
            ]);
        }
    }
}
