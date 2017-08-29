<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Event\Test\Unit;

use Hoa\Event as LUT;
use Hoa\Event\Event as SUT;
use Hoa\Test;

/**
 * Test suite of the event class.
 */
class Event extends Test\Unit\Suite
{
    public function case_multiton(): void
    {
        $this
            ->given($eventId = 'hoa://Event/Test')
            ->when($result = SUT::getEvent($eventId))
            ->then
                ->object($result)
                    ->isInstanceOf(SUT::class)
                ->object(SUT::getEvent($eventId))
                    ->isIdenticalTo($result);
    }

    public function case_register_source_instance(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \Mock\Hoa\Event\Source()
            )
            ->when($result = SUT::register($eventId, $source))
            ->then
                ->variable($result)
                    ->isNull()
                ->boolean(SUT::eventExists($eventId))
                    ->isTrue();
    }

    public function case_register_source_name(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = 'Mock\Hoa\Event\Source'
            )
            ->when($result = SUT::register($eventId, $source))
            ->then
                ->variable($result)
                    ->isNull()
                ->boolean(SUT::eventExists($eventId))
                    ->isTrue();
    }

    public function case_register_redeclare(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \Mock\Hoa\Event\Source(),
                SUT::register($eventId, $source)
            )
            ->exception(function () use ($eventId, $source): void {
                SUT::register($eventId, $source);
            })
                ->isInstanceOf(LUT\Exception::class);
    }

    public function case_register_not_a_source_instance(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \StdClass()
            )
            ->exception(function () use ($eventId, $source): void {
                $result = SUT::register($eventId, $source);
            })
                ->isInstanceOf(LUT\Exception::class);
    }

    public function case_register_not_a_source_name(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = 'StdClass'
            )
            ->exception(function () use ($eventId, $source): void {
                $result = SUT::register($eventId, $source);
            })
                ->isInstanceOf(LUT\Exception::class);
    }

    public function case_unregister(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \Mock\Hoa\Event\Source(),
                SUT::register($eventId, $source)
            )
            ->when($result = SUT::unregister($eventId))
            ->then
                ->boolean(SUT::eventExists($eventId))
                    ->isFalse();
    }

    public function case_unregister_hard(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \Mock\Hoa\Event\Source(),
                SUT::register($eventId, $source),
                $event = SUT::getEvent($eventId)
            )
            ->when($result = SUT::unregister($eventId, true))
            ->then
                ->boolean(SUT::eventExists($eventId))
                    ->isFalse()
                ->object(SUT::getEvent($eventId))
                    ->isNotIdenticalTo($event);
    }

    public function case_unregister_not_registered(): void
    {
        $this
            ->given($eventId = 'hoa://Event/Test')
            ->when($result = SUT::unregister($eventId))
            ->then
                ->variable($result)
                    ->isNull();
    }

    public function case_attach(): void
    {
        $this
            ->given(
                $event    = SUT::getEvent('hoa://Event/Test'),
                $callable = function (): void {
                }
            )
            ->when($result = $event->attach($callable))
            ->then
                ->object($result)
                    ->isIdenticalTo($event)
                ->boolean($event->isListened())
                    ->isTrue();
    }

    public function case_detach(): void
    {
        $this
            ->given(
                $event    = SUT::getEvent('hoa://Event/Test'),
                $callable = function (): void {
                },
                $event->attach($callable)
            )
            ->when($result = $event->detach($callable))
            ->then
                ->object($result)
                    ->isIdenticalTo($event)
                ->boolean($event->isListened())
                    ->isFalse();
    }

    public function case_detach_unattached(): void
    {
        $this
            ->given(
                $event    = SUT::getEvent('hoa://Event/Test'),
                $callable = function (): void {
                }
            )
            ->when($result = $event->detach($callable))
            ->then
                ->object($result)
                    ->isIdenticalTo($event)
                ->boolean($event->isListened())
                    ->isFalse();
    }

    public function case_is_listened(): void
    {
        $this
            ->given($event = SUT::getEvent('hoa://Event/Test'))
            ->when($result = $event->isListened())
            ->then
                ->boolean($event->isListened())
                    ->isFalse();
    }

    public function case_notify(): void
    {
        $self = $this;

        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \Mock\Hoa\Event\Source(),
                $bucket  = new LUT\Bucket(),

                SUT::register($eventId, $source),
                SUT::getEvent($eventId)->attach(
                    function (LUT\Bucket $receivedBucket) use ($self, $source, $bucket, &$called): void {
                        $called = true;

                        $this
                            ->object($receivedBucket)
                                ->isIdenticalTo($bucket)
                            ->object($receivedBucket->getSource())
                                ->isIdenticalTo($source);
                    }
                )
            )
            ->when($result = SUT::notify($eventId, $source, $bucket))
            ->then
                ->variable($result)
                    ->isNull()
                ->boolean($called)
                    ->isTrue();
    }

    public function case_notify_unregistered_event_id(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \Mock\Hoa\Event\Source(),
                $data    = new LUT\Bucket()
            )
            ->exception(function () use ($eventId, $source, $data): void {
                SUT::notify($eventId, $source, $data);
            })
                ->isInstanceOf(LUT\Exception::class);
    }

    public function case_event_exists(): void
    {
        $this
            ->given(
                $eventId = 'hoa://Event/Test',
                $source  = new \Mock\Hoa\Event\Source(),
                SUT::register($eventId, $source)
            )
            ->when($result = SUT::eventExists($eventId))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_event_not_exists(): void
    {
        $this
            ->given($eventId = 'hoa://Event/Test')
            ->when($result = SUT::eventExists($eventId))
            ->then
                ->boolean($result)
                    ->isFalse();
    }
}
