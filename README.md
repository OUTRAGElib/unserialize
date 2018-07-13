# unserialize

So, PHP has a way to unserialise stuff. But, due to a certain method that was employed to fix a bug with unserialising data that might cause crashes and/or RCEs, one cannot change how an object is serialised... until now.

I'm not particularly that great with how the internals of PHP work (so I couldn't say... make this a plugin) but what I have done instead for you absolutely insane folk is to have this as a class, which one can import using composer - and unserialise between objects that have used `__sleep` or `__wakeup` in the past, along with objects that for whatever reason no longer use the `Serializable` interface.

The method of use is exactly the same as normal serialize, as so:

    <?php
    
    class A
    {
        public $hello = "world";
    }
    
    $serialized = serialize(new A());
    
    $object = (new \OUTRAGElib\Unserialize\Parser())->unserialize($serialized);

Of course, one would not notice any difference in this scenario, but, what if due to development reasons the class was changed to implement `Serializable`? Any call to `unserialize` would normally return `false` but this library is able to work around this.

In addition, in case one would like to just have a new copy of the serialized output, call the `\OUTRAGElib\Unserialize\Parser::parse` method - it behaves the same as the `unserialize` method but without actually turning string into object code.

I haven't yet got around to adding in some tests but this script will happily unserialize a very large document rather quickly, given the excessive overheads of literally tokenising something several times.

Anyhow, I'm releasing this with the MIT licence - but if you do actually find this useful (instead of what I'm planning on using this for) feel free to give me a shout, I'll be pleased with that.

(Fixes PHP bug: https://bugs.php.net/bug.php?id=76606)