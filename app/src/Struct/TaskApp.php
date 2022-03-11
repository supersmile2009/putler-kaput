<?php

namespace App\Struct;

enum TaskApp: string
{
    case BOMBARDIER = 'bombardier';
    case DRIPPER = 'dripper';
    case DNSPERF = 'dnsperf';
}
