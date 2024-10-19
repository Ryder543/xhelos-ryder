// $Id:

This module hooks into the D6 theme registry to override the core message display.

The purr messages, in their default state, look similar to Growl messages on OS X and float in their own jquery based windows. 
The module makes use of the purr jquery function created by Net Perspective: http://net-perspective.com/

------------------------------------------------------

Usage:

Simply install the module and the core message system will be overridden. If javascript is turned off the messages revert to the usual core ones.
There is an admin page at /admin/settings/purr which allows you to change various settings. A default set has been included to give you a start.

------------------------------------------------------

Customisation:

To customise the display of the messages simply copy the folder called 'purrcss' from the module's folder and place the copy in your theme folder.
You can then make adjustments to the copied purr.css and images as you see fit.

------------------------------------------------------

Known issues:

IE6 & 7 aren't able to fade pngs with alpha so the code checks to see whether IE is being used and also for the existence of the setting: usingTransparentPNG:true.
In which case it simply shows and hides the messages rather than using the gradual fade technique. Not as pretty but hey, its IE after all.

------------------------------------------------------

This module was written by Tanc. It uses code written by Net Perspective.