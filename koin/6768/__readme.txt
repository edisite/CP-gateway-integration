NOTES

created by hengky irawan
last modification 2012-05-09 19:00


Rules of PHP files naming (this rule applied for all files except for this __readme.txt):

1) PHP files that it's name begin with underscore is a PHP file that used as included files
   * these PHP files is not published to partner
   * these PHP files is not accessed from outside (operator/partner)
   * example:
     _foul_words.php
     _axis_keyword2sid.php

2) PHP files that it's name begin with underscore and followed by operator's name (or partner's name) is used as included files
   and specific to that operator (or partner)
   * these PHP files is not published to partner
   * these PHP files is not accessed from outside (operator/partner)
   * these PHP files is specific for named operator (or partner)
   * example:
     _axis_keyword2sid.php
     this PHP file only used for Axis operator, please do not use it for another operator
     this PHP file only used/included inside another Axis's PHP files, such as axis_mo.php or axis_mt.php or axis_dr.php

3) _main_mo.php and _main_mt.php and _main_dr.php IS EXCEPTION FROM RULES #1 AND #2
   * these files is used if there is a partner which handle multiple operators, and
   * partner want to communicate with same PHP files for all operators
   * example:
     partner handling 2 operators (axis and isat) but partner want to communicate with same MO PHP files, same MT PHP files,
     same DR PHP files for both operators.
   * so all _main_?.php will require 1 parameter named "operator" - then these _main_?.php will pass incoming request to
     existing individual PHP file based on parameter "operator"

4) Other PHP files generally will have file name [operator]_[mo/mt/dr].php
   * example:
     axis_mo.php
     axis_mt.php
     axis_dr.php
   * these PHP files is published and will be accessed by operator/partner


-END OF DOCUMENT-