# NIHR BioResource Number Generator Extension

This extension generates several numbers to be used at NIHR BioResource Number Generator. It does this for the following numbers:

* Study Participant ID - a sequence number indicating a volunteer in a study.
* Study number - a sequence of the study numbers
* BioResource ID - A random number indicating the contact
* Participant ID - A sequence of volunteers

See https://civicoop.plan.io/projects/nihr-bioresource-for-translational-research-civicrm/wiki/ID_formats for an explanation
of the different numbers.

You also need the following extensions:

* nihrconfigitems
* nihrbackbone
* de.systopia.identitytracker

## Before installing this extension

Before you can install this extension you have to create an _Centre_ organization with the name *NIHR BioResource Centre Cambridge*.

## What if the Cambridge Centre is renamed?

Change _Nihrnumbergenerator/Config.php_ to reflect the new name.

## Waht if I rename a custom field or custom group containing an ID?

Change _Nihrnumbergenerator/Config.php_ to reflect the new changes.

