<?php

namespace App\Struct;

enum TaskDriver: string
{
    // Not supported (yet?)
    // Not implementing for now to avoid DinD complications
//    case DOCKER = 'docker';
//    case DOCKER_SWARM = 'docker_swarm';
    case EXEC = 'exec';
}
