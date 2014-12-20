About
-----

This is Util component from Maketok lib. Includes few useful classes.


Example
-------

StreamHandler


    $writer = new StreamHandler();
    $writer->writeWithLock('test', 'text.txt');
    $writer->delete('text.txt');
    
    $writer->lock('text1.txt');
    $writer2 = new StreamHandler();
    $writer2->lock('text1.txt'); // returns false



License
-------

[MIT](http://opensource.org/licenses/MIT)

