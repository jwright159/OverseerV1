# The Overseer Project

This is The Overseer Project, Version 1 code, as it sits on the live build. The current state 
(26/03/2016) of the code mirrors the "final" official version of the project before deprecation
in favour of version 2. This repository will be mirrored to a non-changing repository for posterity.

By releasing this code, we hope that you can gain an appreciation for complexity of the project, and 
learn from our mistakes.

NOTE: V1 was written by a bunch of amateurs, learning from scratch. Code documentation may be sparse,
and the code itself may be terrible. You've been warned. 

## Installation

First up, you're gonna need a webserver. On Linux, this can be Nginx or Apache. You'll also need PHP, 
and mysqli. For Windows, you can use XAMPP to set it all up easily.

Once that's all installed, it's time to set up the database. Import `install/Overseerv1.sql` - this
contains everything you need to get started, including items. Set up users etc. 

Next, edit `.env.dist` with your database info, then rename it to `.env`, removing the `.dist`.

Install [Composer](https://getcomposer.org/), then open up a command prompt in the root folder and run `composer install`.

That should be about it. 

Have fun - we're looking forward to seeing what you come up with. 

- Thellere (+ bootlegged by jadedDraconevix)