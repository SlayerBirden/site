About
-----

This is Util component from Maketok lib. Includes few useful classes.


Tech
----

StreamHandlerInterface:

```
    public function write($data, $path = null);
    public function writeWithLock($data, $path = null);
    public function read($length = null, $path = null);
    public function delete($path = null, $includeDirectories = false);
    public function lock($path = null);
    public function unLock($path = null);
    public function setPath($path);
    public function eof();
    public function close();
```


License
-------

[GPL 3.0](https://www.gnu.org/licenses/gpl-faq.html)

