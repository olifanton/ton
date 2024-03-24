<?php declare(strict_types=1);

// The MIT License (MIT)
//
// Copyright (c) 2015 Oleksandr Bushkovskyi
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

namespace Olifanton\Ton\Connect\Sse;

class Event implements \Stringable
{
    const END_OF_LINE = "/\r\n|\n|\r/";

    private string $data;

    private string $eventType;

    private ?string $id = null;

    public function __construct(
        string $data = "",
        string $eventType = "message",
        ?string $id = null,
    )
    {
        $this->data = $data;
        $this->eventType = $eventType;
        $this->id = $id;
    }

    public static function parse(string $raw): self
    {
        $event = new self();
        $lines = preg_split(self::END_OF_LINE, $raw);

        foreach ($lines as $line) {
            $matched = preg_match('/(?P<name>[^:]*):?( ?(?P<value>.*))?/', $line, $matches);

            if (!$matched) {
                throw new \InvalidArgumentException(sprintf('Invalid line %s', $line));
            }

            $name = $matches['name'];
            $value = $matches['value'];

            if ($name === "") {
                // ignore comments
                continue;
            }

            switch ($name) {
                case "event":
                    $event->eventType = $value;
                    break;

                case "data":
                    $event->data = empty($event->data) ? $value : "$event->data\n$value";
                    break;

                case "id":
                    $event->id = $value;
                    break;
            }
        }

        return $event;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function __toString()
    {
        return sprintf(
            "[Event] type: %s, id: %s, data: %s",
            $this->eventType,
            $this->id !== null ? $this->id : "NULL",
            $this->data,
        );
    }
}
