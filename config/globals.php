<?php

return [
    'demo_categories' => [
        'UV Speed', 'UV Max', 'NM Speed', 'NM 100S', 'Pacifist', 'UV Fast',
        'Tyson', 'Reality', 'UV Respawn', 'Stroller', 'NoMo', 'NoMo 100S',
    ],
    'short_codes' => [
        'UV Speed'     => '',
        'UV Max'       => 'm',
        'UV Fast'      => 'f',
        'UV Respawn'   => 'r',
        'UV Tyson'     => 't',
        'Pacifist'     => 'p',
        'NM Speed'     => 'n',
        'NM 100S'      => 's',
        'NoMo'         => 'o',
        'NoMo 100S'    => 'os',
        'Stroller'     => 'str',
        'Collector'    => 'col',
        'Other'        => '',
    ],
    'demo_form_inputs' => [
        'UV Speed'    => '<input type="hidden" name="skill" value="4" />',
        'UV Max'      => '<input type="hidden" name="skill" value="4" />',
        'UV Fast'     => '<input type="hidden" name="skill" value="4" /><input type="hidden" name="runflag" value="fast" />',
        'UV Respawn'  => '<input type="hidden" name="skill" value="4" /><input type="hidden" name="runflag" value="respawn" />',
        'Tyson'       => '<input type="hidden" name="skill" value="4" />',
        'Pacifist'    => '<input type="hidden" name="skill" value="4" />',
        'Reality'     => '<input type="hidden" name="skill" value="4" />',
        'Stroller'    => '<input type="hidden" name="skill" value="4" /><input type="hidden" name="runflag" value="stroller" />',
        'NM Speed'    => '<input type="hidden" name="skill" value="5" />',
        'NM 100S'     => '<input type="hidden" name="skill" value="5" />',
        'NoMo'        => '<input type="hidden" name="skill" value="4" /><input type="hidden" name="runflag" value="nomonsters" />',
        'NoMo 100S'   => '<input type="hidden" name="skill" value="4" /><input type="hidden" name="runflag" value="nomonsters" />',
    ],

];
