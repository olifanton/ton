<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Tvm;

abstract class TvmStackEntry
{
    protected string $type;

    protected mixed $data;

    public function __construct(
        string $type,
        mixed $data,
    )
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
