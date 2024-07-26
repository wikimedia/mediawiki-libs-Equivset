# Release History

## 1.6.1
* build: Bump dev dependencies (Daimona Eaytoy)
* Use PHP Static Data Array by default (Sam Reed)

## 1.6.0
* build: Drop claimed support for utfnormal 1.x and 2.x, we require PHP 7.4 (James D. Forrester)
* Add support for wikimedia/utfnormal 4.0.0 (Derick Alangi)
* Map all Arabic-Indic digits to their European number equivalent (Huji Lee)
* build: Apply latest mediawiki/tools/cookiecutter-library boilerplate (Timo Tijhof)
* Simpler, more strict input file parsing (Thiemo Kreuz)
* Make mappings for circled numbers and such more specific (Thiemo Kreuz)
* Minor fixups to a few direct mappings (Thiemo Kreuz)
* Use createMock shortcut in tests (Thiemo Kreuz)
* Reduce file size of the .json output file (Thiemo Kreuz)
* Prefer mapping lowercase to uppercase characters (Thiemo Kreuz)

## 1.5.0
* Read input file line by line (Thiemo Kreuz)
* Map Ǫ (U+01EA) to Q instead of O (Thiemo Kreuz)
* Add many more Latin-like characters from "extended" Unicode blocks (Thiemo Kreuz)
* Equivset: Optimise load() method to avoid uncached I/O stats (Timo Tijhof)
* Add equivset data as static array file (Umherirrender)
* Add all relevant Roman numeral characters (Thiemo Kreuz)
* Structure input file with comments for each Unicode block (Thiemo Kreuz)
* build: Switch phan to special library mode (James D. Forrester)
* Add various missing characters (Thiemo Kreuz)
* Very minor upgrades to a few more mappings (Thiemo Kreuz)
* Some more specific mappings without change to the output (Thiemo Kreuz)
* tests: fail if files have not been regenerated (Umherirrender)
* Use much more specific mappings whenever possible (Umherirrender)
* Add various letterlike symbols (Umherirrender)
* Add more letterlike symbols (Umherirrender)
* Add characters from the "Phonetic Extensions" Unicode Block (1D00-1DBF) (Umherirrender)
* Add more letterlike symbols (Enclosed Alpha Supplement 1F100–1F1FF) (Umherirrender)
* Add more letterlike symbols (Mathematical Alpha Symbols 1D400-1D7FF) (Umherirrender)
* Add many more Greek characters from the 0370+ and 1F00+ ranges (Thiemo Kreuz)
* Sort characters in each set by Unicode codepoint number (Thiemo Kreuz)
* Merge sets to map all to the final set (Umherirrender)
* Add °¹²³º (Umherirrender)
* Fix confusing sorting order in dist/equivset.txt (Thiemo Kreuz)
* Add more number signs (Umherirrender)
* Ensure hex-ordered equivset.in on generate (Umherirrender)
* Fix a few obviously misplaced mappings (Thiemo Kreuz)
* Add a few more question marks (Thiemo Kreuz)
* Add description to generate-equivset command (Umherirrender)
* Add ¡¥©ªµ×İĴĵŉŊŋ€₱∅ (Umherirrender)
* Add certain "Enclosed Alphanumerics" to equivset (Mdaniels5757)
* Fix inconsistent mapping of Greek η to "n" and sometimes "h" (Thiemo Kreuz)
* Map modified to unmodified Greek characters (Thiemo Kreuz)
* Fix sorting order in equivset.in (Thiemo Kreuz)
* Extend the blacklist of invisible characters (Máté Szabó)
* Use more specific lowercase mappings in input file (Thiemo Kreuz)
* Modernize the code a bit, add strict types, use ?? and such (Thiemo Kreuz)
* Expand set for lower/upper case characters which are alone in the set (Umherirrender)
* build: Updating composer dependencies (Umherirrender)
* build: Pin PHPUnit to 9.5.28 (James D. Forrester)
* tests: Replace logicalOr() with withConsecutive() to be more strict (Umherirrender)
* Fix PHPCS exclusions (Sam Reed)
* build: Don't try to use xdebug, use pcov instead (James D. Forrester)
* build: Updating composer dependencies ([BOT] libraryupgrader)
* build: Drop phpunit ^8.5 compatibility (James D. Forrester)
* [BREAKING CHANGE] Drop PHP 7.2 and PHP 7.3 support (James D. Forrester)
* build: Updating mediawiki/mediawiki-phan-config to 0.12.0 ([BOT] libraryupgrader)
* add missing double strike characters (Voidwalker)
* Remove duplicate entries from equivset.in (Umherirrender)

## 1.4.3
* Minor code cleanup (Sam Reed)
* Equivset::geIterator() add return type (Sam Reed)
* build: Updating composer dependencies ([BOT] libraryupgrader)
* Add phan (Sam Reed)
* build: Updating composer dependencies ([BOT] libraryupgrader)
* build: Updating mediawiki/mediawiki-codesniffer to 37.0.0 ([BOT] libraryupgrader)
* build: Cleanup and improve .phpcs.xml (Umherirrender)
* build: Updating composer dependencies ([BOT] libraryupgrader)
* build: Updating composer dependencies ([BOT] libraryupgrader)

## 1.4.2
* Support wikimedia/utfnormal ^3.0.1 (Kunal Mehta)

## 1.4.1
* build: Upgrade phpunit to ^9.5 for PHP 8.0 compatibility (James D. Forrester)
* build: Updating mediawiki/mediawiki-codesniffer to 34.0.0 ([BOT] libraryupgrader)
* build: Updating ockcyp/covers-validator to 1.3.1 ([BOT] libraryupgrader)
* build: Updating mediawiki/mediawiki-codesniffer to 33.0.0 (Umherirrender)
* Enforce Code Coverage at 100% (David Barratt)
* build: Manually enable xdebug for phpcs (James D. Forrester)
* build: Updating mediawiki/minus-x to 1.1.0 ([BOT] libraryupgrader)
* build: Updating composer dependencies ([BOT] libraryupgrader)
* build: Updating mediawiki/mediawiki-codesniffer to 31.0.0 ([BOT] libraryupgrader)
* README: Fix markdown display of code (DannyS712)
* build: Updating mediawiki/mediawiki-codesniffer to 30.0.0 ([BOT] libraryupgrader)
* Update PHPUnit to 8.5 (Umherirrender)
* build: Updating composer dependencies ([BOT] libraryupgrader)
* build: Update symfony/* packages to ^5 (Umherirrender)
* Follow-up 4f8d6d8082: Also drop .travis.yml reference from .gitattributes (James D. Forrester)
* Update .gitignore to ignore .phpunit.result.cache (Huji Lee)
* Drop Travis testing, no extra advantage over Wikimedia CI and runs post-merge anyway (James D. Forrester)
* build: Updating mediawiki/mediawiki-codesniffer to 29.0.0 ([BOT] libraryupgrader)
* Fix validation warning in composer.json (Kunal Mehta)

## 1.4.0
* Require PHP 7.2+ and update dev dependencies to latest (Daimona Eaytoy)
* build: Updating mediawiki/mediawiki-codesniffer to 28.0.0 ([BOT] libraryupgrader)
* build: Updating mediawiki/mediawiki-codesniffer to 26.0.0 ([BOT] libraryupgrader)
* Drop PHP support pre 7.0 (Sam Reed)
* build: Updating mediawiki/mediawiki-codesniffer to 24.0.0 ([BOT] libraryupgrader)
* Add two more characters to Equivset (Huji Lee)
* build: Updating mediawiki/mediawiki-codesniffer to 23.0.0 ([BOT] libraryupgrader)
* Add 579 new characters (Daimona Eaytoy)
* build: Updating mediawiki/mediawiki-codesniffer to 22.0.0 (Umherirrender)
* build: Updating mediawiki/mediawiki-codesniffer to 21.0.0 ([BOT] libraryupgrader)
* build: Updating mediawiki/mediawiki-codesniffer to 20.0.0 ([BOT] libraryupgrader)
* Sync with library bootstrap (Kunal Mehta)
* Fix typo (MarcoAurelio)
* build: Updating mediawiki/mediawiki-codesniffer to 18.0.0 ([BOT] libraryupgrader)
* build: Updating mediawiki/mediawiki-codesniffer to 17.0.0 ([BOT] libraryupgrader)
* build: Updating jakub-onderka/php-parallel-lint to 1.0.0 ([BOT] libraryupgrader)

## 1.3.0
* Remove invisible space characters from strings (Huji Lee)
* Support utfnormal ^2.0.0 (Kunal Mehta)
* build: Updating phpunit/phpunit to 4.8.36 || ^6.5 ([BOT] libraryupgrader)
* build: Updating mediawiki/mediawiki-codesniffer to 16.0.1 ([BOT] libraryupgrader)
* build: Adding MinusX (Kunal Mehta)
* build: Updating phpunit/phpunit to 4.8.36 ([BOT] libraryupgrader)
* build: Fix .phpcs.xml in .gitattributes (Umherirrender)
* build: Updating mediawiki/mediawiki-codesniffer to 16.0.0 ([BOT] libraryupgrader)
* Use SPDX 3.0 license identifier (Kunal Mehta)
* Improve README display in doxygen (Kunal Mehta)
* expanding description in README (Ryan Kaldari)
* build: Updating mediawiki/mediawiki-codesniffer to 15.0.0 ([BOT] libraryupgrader)

## 1.2.0
* Add missing mappings to Equivset (Dayllan Maza)

## 1.1.0
* Increase Test Coverage to 100% (David Barratt)
* Fix indentation of COPYING (Kunal Mehta)
* Move phpunit.xml to phpunit.xml.dist (Kunal Mehta)
* Improving README.md (Ryan Kaldari)
* readme: Fix `<pre>` for compat with Doxygen Markdown (Timo Tijhof)
* Renaming README to README.md (needed by GitHub) (Ryan Kaldari)

## 1.0.0
* Split off AntiSpoof equivset generation and string normalization into its own library (David Barratt)
* Initial commit (Kunal Mehta)