<?php
namespace App\Enum;

final class DevTaskStatusEnum
{
    use Enum;

    public const BACKLOG = 0;
    public const SELECTED_FOR_DEVELOPMENT = 1;
    public const IN_DEVELOPMENT = 2;
    public const IN_TEST = 3;
    public const RELEASED = 4;
}