![Hoa](http://static.hoa-project.net/Image/Hoa_small.png)

Hoa is a **modular**, **extensible** and **structured** set of PHP libraries.
Moreover, Hoa aims at being a bridge between industrial and research worlds.

# Hoa\Event ![state](http://central.hoa-project.net/State/Event)

This library allows to use events and listeners in PHP. This is an observer
design-pattern implementation.

## Installation

With [Composer](http://getcomposer.org/), to include this library into your
dependencies, you need to require
[`hoa/event`](https://packagist.org/packages/hoa/event):

```json
{
    "require": {
        "hoa/event": "~1.0"
    }
}
```

Please, read the website to [get more informations about how to
install](http://hoa-project.net/Source.html).

## Quick usage

We propose a quick overview of how to use events and listeners.

### Events

An event is:
  * **Asynchronous** when registering, because the observable may not exist yet
    while observers start to observe,
  * **Anonymous** when using, because the observable has no idea how many and
    what observers are observing,
  * It aims at a **large** diffusion of data through isolated components.
    Wherever is the observable, we can observe its data.

In Hoa, an event channel has the following form:
`hoa://Event/LibraryName/AnId:pseudo-class#anAnchor`. For instance, the
`hoa://Event/Exception` channel contains all exceptions that have been thrown.
The `hoa://Event/Stream/StreamName:close-before` contains all streams that are
about to close. Thus, the following example will observe all thrown exceptions:

```php
Hoa\Event\Event::getEvent('hoa://Event/Exception')->attach(
    function (Hoa\Event\Bucket $bucket) {
        var_dump(
            $bucket->getSource(),
            $bucket->getData()
        );
    }
);
```

Because `attach` expects a callable and because Hoa's callable implementation is
smart, we can directly attach a stream to an event, like:

```php
Hoa\Event\Event::getEvent('hoa://Event/Exception')->attach(
    new Hoa\File\Write('Foo.log')
);
```

This way, all exceptions will be printed on the `Foo.log` file.

### Listeners

Contrary to an event, a listener is:
  * **Synchronous** when registering, because the observable must exist before
    observers can observe,
  * **Identified** when using, because the observable knows how many observers
    are observing,
  * It aims at a **close** diffusion of data. The observers must have an access
    to the observable to observe.

The `Hoa\Event\Listenable` interface requires the `on` method to be present to
register a listener to a listener ID. For instance, the following example
listens the `message` listener ID, i.e. when a message is received by the
WebSocket server, the closure is executed:

```php
$server = new Hoa\Websocket\Server(…);
$server->on('message', function (Hoa\Event\Bucket $bucket) {
    var_dump(
        $bucket->getSource(),
        $bucket->getData()
    );
});
```

## Documentation

Different documentations can be found on the website:
[http://hoa-project.net/](http://hoa-project.net/).

## License

Hoa is under the New BSD License (BSD-3-Clause). Please, see
[`LICENSE`](http://hoa-project.net/LICENSE).
