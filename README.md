## generator
Used to generate some template code files.

## 使用说明
> 该工具生成器是用来生成一些IDE helper.
```
[root@host ~]# ./bin/generator
Usage: generator options
  
Options:
  --extension=extension-name Generator code this extension.
  --class=class-name         Generator code this class.
  --output=/tmp              Output file directory path(Default: /tmp).
  --force-namespace          Force use namespace generator code.
  --help                     Print this usage information.
[root@host ~]# mkdir /tmp/swoole
[root@host ~]# ./bin/generator --extension=swoole --output=/tmp/swoole
```

