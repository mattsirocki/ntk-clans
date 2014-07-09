ntk-clans
============
A simple set of scripts for displaying some information about NexusTK clans.

clans.py
--------

    ./clans.py [--update] [--sort KEY]

This script will print out a textual table of clan statistics concerning membership. Specifying KEY will sort the table by the corresponding header. Valid choices for KEY are:
`Clan`, `T`, `R`, `U`, `RT`, `UT`, `A`, `AR`, `AU`, `ART`, `AUT`, `I`, `IR`, `IU`, `IRT`, `IUT`, `X`, `XR`, `XU`, `XRT`, and `XUT`.

kill.py
-------

    ./kill.py <clan> [--update-clan] [--update-users] [--sort]

This script will print out a list of unregistered users and the dates they became unregistered. It uses `users.nexustk` to get a list of users in the given `<clan>` before querying `buddhisanctum.com` to retrieve each member's unregistration date. Results are saved in serialized data files that will not be refreshed unless the corresponding `--update-*` option is passed.
