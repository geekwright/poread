<{$locale='en_US'}>
<html>
  <head>
    <title>Smarty</title>
    <{$dbb1}>
  </head>
  <body>
    Hello, <{$name}>! <br />
    <{_ msgid="A translatable string."}>
    <{_ msgid="A translatable string." domain='system'}>
    <{_ msgid="A translatable string." msgctxt="fred" domain='system' locale=$locale num=2}>
    <{_ msgid="%d string" msgid_plural="%d strings" num=6}>
    <{_ m="A string" mp="%s strings" n=6}>
    <br /><br />
    <{$dbb2}>
  </body>
</html>