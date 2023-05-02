=== Autoresponder Addon for Newsletter ===
Tested up to: 5.9.2

== Changelog ==

* Changed delay validation to allow decimal numbers

= 1.3.7 =

* WP 5.9 compatibility check
* Minor optimizations

= 1.3.6 =

* Fixed CSS inclusion
* Compatibility checked with WP 5.8.3

= 1.3.5 =

* Query error due to missing max emails fixed

= 1.3.4 =

* Fixed convert button on maintenance panel

= 1.3.3 =

* Added the step placeholder for google analytics

= 1.3.2 =

* Compatibility with WP 5.8 meta data
* Added Google Analytics configuration

= 1.3.1 =

* Added support to restart on resubscribe
* Added new lists activation on series completion

= 1.3.0 =

* Thank you for Thomas LEJEUNE for the request and code sample to implement the massive re-enable (see below)
* New subscriber list action to control the subscriber status
* Fixed the immediate series block in some subscription conditions
* Added massive action to re-enable the subscribers who completed the series for new late added steps
* More coherence between panels
* Advanced options and action move to separated panel

= 1.2.9 =

* Fixed delay displayed on email list

= 1.2.8 =

* Fix email sending on second subscription

= 1.2.7 =

* Improved user list
* Added https to gravatar image
* More room for serties with numerous steps

= 1.2.6 =

* More detailed report on autoresponder subscriber list panel
* Filter on subscriber list panel to show the processing or late subscribers
* Cleanup of data of deleted subscribers (which may lead to show a late warning)

= 1.2.5 =

* Improved reporting of late messages

= 1.2.4 =

* Added check for {message} tag in the hand coded theme
* Fix bug not applying the template when testing messages made with the old theme system

= 1.2.3 =

* Fixed the tracking flag

= 1.2.2 =

* Fixed the email status on series duplication

= 1.2.1 =

* Added conversion feature from old series to the new one (with composer)

= 1.2.0 =

* Added on confirmation immediate send of message 1 when the delay is set to zero
* Interface redesign
* Improved statistics report
* Test mode changed: now it must be triggered manually and limited to one single series at time
* Great log details when Newsletter is in debug mode
* In test mode there is a reset all button to make the test easy
* In test mode the number of emails sent are not limited by your set speed (otherwise test could be not easy to read -
keep the number of subscribers in a series small when doing tests)
* Somewhere changed the terminology to be more clear
* Added warning if the autoresponder is cumulating delays

= 1.1.4 =

* Duplication fix
* Number of steps on autoresponder list

= 1.1.3 =

* Added action on subscriber list to reset the status or restart the series
* Menu icon fix

= 1.1.2 =

* Fixed menu for editors

= 1.1.1 =

* Fixed step deletion bug

= 1.1.0 =

* Improved theme configuration and preview

= 1.0.8 =

* Added hand-coded theme

= 1.0.7 =

* Fixed autoresponder processing which does not proceed in a specific case

= 1.0.6 =

* Fix the autoresponder list

= 1.0.5 =

* Fix delay not keeping the zero value
* Fix debug notice 

= 1.0.3 =

* Fix

= 1.0.0 =

* First release
