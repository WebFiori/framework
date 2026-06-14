# Changelog

## [3.0.0](https://github.com/WebFiori/framework/compare/v3.0.0-RC.5...v3.0.0) (2026-06-14)


### Features

* **access:** Access::can() fallback to user getRoles() ([a041a41](https://github.com/WebFiori/framework/commit/a041a4133a0b828a0d55653955cbf84371eb30fb)), closes [#381](https://github.com/WebFiori/framework/issues/381)
* add dynamic namespace routing and services:list command ([e2446f6](https://github.com/WebFiori/framework/commit/e2446f602fcd8c99e28e97b3d0cea2ab23cab663)), closes [#383](https://github.com/WebFiori/framework/issues/383) [#385](https://github.com/WebFiori/framework/issues/385)
* add recursive scanning and kebab-case route derivation ([86f3507](https://github.com/WebFiori/framework/commit/86f3507cedf81a2a0265992aa8fb384780a87608))
* add route caching for production performance ([8f978c5](https://github.com/WebFiori/framework/commit/8f978c565c0364c9ea3a7af99af1c305c2495fd5)), closes [#386](https://github.com/WebFiori/framework/issues/386)
* add ServiceRouter::discover() for auto-registering API routes ([24f3fdd](https://github.com/WebFiori/framework/commit/24f3fdd610c8c12a00fd13e651171e9bada2fa1e)), closes [#382](https://github.com/WebFiori/framework/issues/382) [#384](https://github.com/WebFiori/framework/issues/384)
* **health:** add getChecks() and afterAll() lifecycle hook ([149d510](https://github.com/WebFiori/framework/commit/149d5106182aa39c3eccbd8feb0967776c2feabe)), closes [#392](https://github.com/WebFiori/framework/issues/392) [#391](https://github.com/WebFiori/framework/issues/391)
* **middleware:** auto-resolve transitive dependencies from registry ([35763a9](https://github.com/WebFiori/framework/commit/35763a9d8641fa233489bee0e162a8b527e79ab5)), closes [#380](https://github.com/WebFiori/framework/issues/380)
* **migrations:** add migrations:step command for interactive execution ([390d9fb](https://github.com/WebFiori/framework/commit/390d9fb607f8bef39859faeb6fb516902b160bf2)), closes [#387](https://github.com/WebFiori/framework/issues/387)
* **session:** add CacheSessionStorage driver ([2496203](https://github.com/WebFiori/framework/commit/2496203d6581aad33006f69017de383f347b446c)), closes [#337](https://github.com/WebFiori/framework/issues/337)


### Bug Fixes

* **middleware:** fix CacheMiddleware compatibility with http v6.0 ([ce6dcff](https://github.com/WebFiori/framework/commit/ce6dcffab486fc9f580057dd903d36ca36568734)), closes [#289](https://github.com/WebFiori/framework/issues/289)
* **session:** reuse cookie ID when session storage is empty ([28f77b4](https://github.com/WebFiori/framework/commit/28f77b4feeefc3d44657347742966bbecd2458ae)), closes [#389](https://github.com/WebFiori/framework/issues/389) [#388](https://github.com/WebFiori/framework/issues/388)
* **test:** add clearstatcache() before class_exists in AddLangCommandTest ([ba5502b](https://github.com/WebFiori/framework/commit/ba5502b97e3fe02c85e014bf36aa6b85f27e0c4a))
* **test:** fix flaky StartSessionMiddleware test on PHP 8.1 ([369d756](https://github.com/WebFiori/framework/commit/369d756960fa3046ef5c7750c5d39d3b188cc9a6))
* **test:** make testRegisterDuplicate self-contained ([b982bd6](https://github.com/WebFiori/framework/commit/b982bd64dfcf356cab2b573f7cb4dbf646c0c45d))
* **test:** update HelpCommandTest for new routes and services commands ([806aaf5](https://github.com/WebFiori/framework/commit/806aaf52d64ffb31361054736f08a898a76952ab))


### Miscellaneous Chores

* Merge pull request [#400](https://github.com/WebFiori/framework/issues/400) from WebFiori/dev ([9ae069c](https://github.com/WebFiori/framework/commit/9ae069cba059590c21a825fe21870d83c04f7664))
* Updated License Headers ([e33c04d](https://github.com/WebFiori/framework/commit/e33c04d8b62882499ea76e67082587156cd54948))

## [3.0.0-RC.5](https://github.com/WebFiori/framework/compare/v3.0.0-RC.5...v3.0.0-RC.5) (2026-05-31)


### ⚠ BREAKING CHANGES

* Scheduler web UI and APIs removed. Use CLI commands (php webfiori scheduler) for task management instead.
* WebFiori\Framework\Util class has been removed. All methods were either unused, duplicated by existing framework methods, or trivially inlineable.
* Removed MySQLSessionsTable, MSSQLSessionsTable, MySQLSessionDataTable, and MSSQLSessionDataTable classes. Use
* **session:** SessionStorage::gc() signature changed from gc() to gc(string $olderThan, int $maxCount = 0). Custom storage implementations must update their gc() method signature.
* **deps:** Application code using Cache::get(), Cache::set(),

### Features

* (cli): Run Seeders With Migrations ([7796e93](https://github.com/WebFiori/framework/commit/7796e935b04e299d85958f5646b39a2f026ecdfc))
* Add `env:` to Class Driver ([89cd39d](https://github.com/WebFiori/framework/commit/89cd39d0f48c558a471a6351218539a8c72f30b2))
* Add `property` and `constant` ([bebe151](https://github.com/WebFiori/framework/commit/bebe151ebf59b7612b4f064c5dfddcd926e7fd82))
* Add Arguments Support in Add DB Connection Command ([8223428](https://github.com/WebFiori/framework/commit/8223428c2ed1c67fc9eeb6828ec65d66627067a7))
* add CORS middleware ([75d4048](https://github.com/WebFiori/framework/commit/75d404817084838521af79c4ca93f137b4e93402)), closes [#341](https://github.com/WebFiori/framework/issues/341)
* add CSRF protection middleware ([9171c38](https://github.com/WebFiori/framework/commit/9171c38e1672651ea62682186e2a39cdea96b1b6)), closes [#333](https://github.com/WebFiori/framework/issues/333)
* Add Dependencies and Envs ([e3950b6](https://github.com/WebFiori/framework/commit/e3950b601a218abee6296bef73bb3adabd9165c6))
* add dependency-based middleware ordering ([cc8c201](https://github.com/WebFiori/framework/commit/cc8c201772f76df16c0bae25a1758a9b81f824fa)), closes [#334](https://github.com/WebFiori/framework/issues/334)
* add FileAccessStorage and DatabaseAccessStorage for RBAC ([cc55249](https://github.com/WebFiori/framework/commit/cc5524909beed03d9748d9dab8bb74bc1240de1c))
* add health check endpoint with auto-discovery ([2b1701d](https://github.com/WebFiori/framework/commit/2b1701d3145e0d5f5a82b995555ab27298aeb7cd)), closes [#339](https://github.com/WebFiori/framework/issues/339)
* add HTTP response caching middleware (ETag / Cache-Control) ([ae9b1ad](https://github.com/WebFiori/framework/commit/ae9b1ad6844562bdcd02722f046d328755772f75)), closes [#352](https://github.com/WebFiori/framework/issues/352)
* add maintenance mode (down/up commands) ([9d5202c](https://github.com/WebFiori/framework/commit/9d5202c24cf493685dbda545ca5318da5ac012f9)), closes [#351](https://github.com/WebFiori/framework/issues/351)
* Add New Method" `method` ([4d486b2](https://github.com/WebFiori/framework/commit/4d486b27446d6d6e53a0d2e54a23e09b8d6e40bc))
* add rate limiting middleware ([8db4e79](https://github.com/WebFiori/framework/commit/8db4e79873a7decba506d837a160e706b5b448c9)), closes [#335](https://github.com/WebFiori/framework/issues/335)
* add RBAC/ABAC system with backward compatibility ([82b43ba](https://github.com/WebFiori/framework/commit/82b43ba4b8ee6b2a604fdaa7838b48f5215e69b3))
* Added `getCode` ([ff37c27](https://github.com/WebFiori/framework/commit/ff37c27169234495d3fe8abc1a318ca039521535))
* Added `migrations:fresh` Command ([d5f732b](https://github.com/WebFiori/framework/commit/d5f732b219375f8c795bc5ed4941d89f14f07594))
* Added a Script to Update Version ([e998c47](https://github.com/WebFiori/framework/commit/e998c476b2629b6b8307d6ad52813cdc1a63eb05))
* Added Options to `AttributeTableWriter` ([1d6340c](https://github.com/WebFiori/framework/commit/1d6340c93cffc8b90ab3d3b99179ceddb335a355))
* Added Scan for Seeders in Dry Run ([a2697f8](https://github.com/WebFiori/framework/commit/a2697f837b193a54e2f82cd14935f871748facf5))
* Added Support For Args in Add SMTP Conn ([4554cca](https://github.com/WebFiori/framework/commit/4554cca7730f64cc5dc1ee781790f4c081ce3d80))
* Added Support for Removing Callbacks ([6e83132](https://github.com/WebFiori/framework/commit/6e831324ec56b8f1324f44275109f9538b56623b))
* allow middleware instances in route definitions ([76a3dc6](https://github.com/WebFiori/framework/commit/76a3dc6a36447d339a3680a2144aea06f2d5b562)), closes [#340](https://github.com/WebFiori/framework/issues/340)
* Attributes ([9e11d2b](https://github.com/WebFiori/framework/commit/9e11d2be039a04ea2b0fa6797cd463e03b146584))
* Chaining ([d3b2407](https://github.com/WebFiori/framework/commit/d3b24077f1652396226152581f043d8bc323c15a))
* **cli:** add --all-connections flag and connection validation for migrations ([5a4daea](https://github.com/WebFiori/framework/commit/5a4daea0bed464f43980c273ff5d8f9b2f7967db)), closes [#326](https://github.com/WebFiori/framework/issues/326)
* **cli:** add migrations:skip command for baselining ([4cbde82](https://github.com/WebFiori/framework/commit/4cbde826774f2a533278e4d2b98f523c65fa42d7)), closes [#320](https://github.com/WebFiori/framework/issues/320)
* Code Reuse Helpers ([f54ed91](https://github.com/WebFiori/framework/commit/f54ed91850136e334b6092e27c797ace2cf6a63c))
* Create Attributes Table ([6f4a7fc](https://github.com/WebFiori/framework/commit/6f4a7fc315667081ca22893068a4f744003b7344))
* Create Background Task Command ([fab0c39](https://github.com/WebFiori/framework/commit/fab0c39acd3ae9f88a033e43122a7b0938880ca5))
* Create CLI Command ([3c88ecf](https://github.com/WebFiori/framework/commit/3c88ecf2b547e9e55e652800e63d8c73fbe97dcf))
* Create CRUD Resource ([78d5ecf](https://github.com/WebFiori/framework/commit/78d5ecf1986e4050ab70709f304816afabc18433))
* Create Domain Entity ([3c4944c](https://github.com/WebFiori/framework/commit/3c4944c4c4a0696784f6b1b1c7b47d4d7c38d56a))
* Create Middleware Command ([2b189be](https://github.com/WebFiori/framework/commit/2b189be7fc0404942db188cbd60d5a6be01f43e2))
* Create Migration ([989cf6d](https://github.com/WebFiori/framework/commit/989cf6df71b2b3c50256b039a8ea19957e1c6cdd))
* Create Repo ([498c214](https://github.com/WebFiori/framework/commit/498c2141fd48ed23acc79e9cf08e3bfc73265fd9))
* Create Seeder ([b3038d0](https://github.com/WebFiori/framework/commit/b3038d09b671a60957bece8c24c1bf25169a0c9f))
* Create Table Command ([6fe3be5](https://github.com/WebFiori/framework/commit/6fe3be5486c6f98ec8409a644ad15d8ec5f96cd0))
* **deps:** upgrade webfiori/cache from v2 to v3 ([c24817d](https://github.com/WebFiori/framework/commit/c24817d4f9794cca5a0f32b4b64c096dde565f9e)), closes [#301](https://github.com/WebFiori/framework/issues/301)
* Doc-block Builder ([0eb7760](https://github.com/WebFiori/framework/commit/0eb7760ea9107f5a004f30e2b2d0b5526e3eb9ac))
* Env Vars for Json Driver ([f28f977](https://github.com/WebFiori/framework/commit/f28f9776e7b19d91b352ef1e3fb4a1cc7a793674))
* integrate event system into framework ([d1bed19](https://github.com/WebFiori/framework/commit/d1bed19a80d8942ca56f4811c817e2790cf073e7)), closes [#347](https://github.com/WebFiori/framework/issues/347)
* integrate queue system into framework ([7eff48d](https://github.com/WebFiori/framework/commit/7eff48ddacdd26141b2920f0ddbfe477eb235d1f)), closes [#349](https://github.com/WebFiori/framework/issues/349)
* integrate webfiori/container into framework ([2b07a02](https://github.com/WebFiori/framework/commit/2b07a027627a2ff8e46319b82407977c28923dbf)), closes [#345](https://github.com/WebFiori/framework/issues/345)
* integrate webfiori/log into framework ([c596620](https://github.com/WebFiori/framework/commit/c5966208e29b542624471073f5dbf2fcf4a23205)), closes [#336](https://github.com/WebFiori/framework/issues/336)
* **migrations:** enable recursive discovery of migrations in subdirectories ([b855c18](https://github.com/WebFiori/framework/commit/b855c18ce8ef134efefa18e9984986efa3704fe0)), closes [#317](https://github.com/WebFiori/framework/issues/317)
* Optional Config Var ([b34cd18](https://github.com/WebFiori/framework/commit/b34cd182ae731d9b814b3362da1332b103e08277))
* Rollback Migration on Error ([e78d372](https://github.com/WebFiori/framework/commit/e78d372087ed9a167415ac2cd6584b11a71aa060))
* Rollback of Migrations ([078c94f](https://github.com/WebFiori/framework/commit/078c94f98b090176b22e2354e326be7153258663))
* **scheduler:** add scheduler:daemon command with time-limited execution ([3d3cb1d](https://github.com/WebFiori/framework/commit/3d3cb1d6ddb3aa35828935a34650b67744375391)), closes [#300](https://github.com/WebFiori/framework/issues/300)
* **scheduler:** Added Support for `env:` Syntax ([8240078](https://github.com/WebFiori/framework/commit/824007895f60ba0bb6c774c7ceb4dfe15dc987f6))
* Split Migrations Count from Seeders Count ([69c69d6](https://github.com/WebFiori/framework/commit/69c69d655ae0473d46a783cbeb04f97e326c90df))
* Web Services Writer ([ff39f02](https://github.com/WebFiori/framework/commit/ff39f02ddadcfcbef0bccd9d3c3dc38fec1c49eb))


### Bug Fixes

* Access Modifier ([95016a8](https://github.com/WebFiori/framework/commit/95016a88a419eeed43e7e02cce70ac3bfbe16754))
* API Creation ([d3c384f](https://github.com/WebFiori/framework/commit/d3c384f48f61c828cf9358cbb1675e26be25b0ee))
* App Directories Creation ([a8ee667](https://github.com/WebFiori/framework/commit/a8ee6672e2758e919dc7b2bfcc6e8d3718cab82f))
* Auto-Reg With RegEx ([1d99fb8](https://github.com/WebFiori/framework/commit/1d99fb815adfccad978d4210cf5c6535ae858657))
* Auto-Register on Attributes ([52898f9](https://github.com/WebFiori/framework/commit/52898f9def1d0ad92670e6ab2b18420c2f07a7aa)), closes [#313](https://github.com/WebFiori/framework/issues/313)
* Bug in Finding Class Loader Path ([6831a96](https://github.com/WebFiori/framework/commit/6831a96d91962480b097561b559e2ecf07af1c0b))
* Check for Empty `--class-name` ([e5f845c](https://github.com/WebFiori/framework/commit/e5f845cdeea492ba59d0069da0c06114d8badc37))
* **ci:** suppress abstract class warning and show skip reasons in CI ([50b1d88](https://github.com/WebFiori/framework/commit/50b1d887bdf283d8915cb851c0df9d04874e2e9a))
* CLI Test Case Class Fix ([ac64c01](https://github.com/WebFiori/framework/commit/ac64c0180521c036a47bfb7608baecd988edd116))
* **cli:** Deleting All Records of Changes Table ([61d2107](https://github.com/WebFiori/framework/commit/61d2107bf05aef515a92cd19df80a903bcc776a8))
* **cli:** object-to-string conversion errors ([e1a6e81](https://github.com/WebFiori/framework/commit/e1a6e810a3ac4919934a7b61b3ca68a4108c393e))
* **cli:** Run Migrations With No Tables Check ([b5e9f06](https://github.com/WebFiori/framework/commit/b5e9f067a8f0848095af373ad2eab4b5cf4a69bc))
* Command Writer ([338e8d2](https://github.com/WebFiori/framework/commit/338e8d2d7e72e66463bb8d57a6b34f20aa38ff3b))
* correct parent constructor call and static method usage in ExtendedWebServicesManager ([b110a38](https://github.com/WebFiori/framework/commit/b110a38fd8cae51b2596fa02180e21e28e03acc0)), closes [#296](https://github.com/WebFiori/framework/issues/296)
* Fix Session Tables Creation ([f318ba4](https://github.com/WebFiori/framework/commit/f318ba4204c45d49717b7dea85f5fbda5645b070))
* Fix Test Case ([6b26e2f](https://github.com/WebFiori/framework/commit/6b26e2f023cea94eb7fdf2a665f1a721e5e190eb))
* Fix Test Cases ([1086336](https://github.com/WebFiori/framework/commit/108633622e1938699360b45d65d3565bcf611e85))
* Fix Test Cases ([0c914de](https://github.com/WebFiori/framework/commit/0c914de97f37eef1bf862c4b37f323470dfcf5ef))
* Fix to Create Migration with Defaults ([534845d](https://github.com/WebFiori/framework/commit/534845df19c2fe7f2bec8559b77e47576c04313a))
* Fixes and Tests Refactoring ([5ff31ab](https://github.com/WebFiori/framework/commit/5ff31ab5dc2c33b8a9389f3bf33001a525f57a7d))
* Getting Arg Value in CLI ([c56b9bf](https://github.com/WebFiori/framework/commit/c56b9bf6f34617336ad1f750943f705d84357ba0))
* Getting Requested URI ([79f47a8](https://github.com/WebFiori/framework/commit/79f47a8682a5ffd15c604b6e9138ec912c391c3a))
* Initialization Path ([192c2f0](https://github.com/WebFiori/framework/commit/192c2f02ea2da20e28ed577c6211bc17f4052b4f))
* Max/Min Values Issue ([b57b6bc](https://github.com/WebFiori/framework/commit/b57b6bc51de36c579cd9a3dd1ebc065e06be18fe))
* Metadata ([84facc3](https://github.com/WebFiori/framework/commit/84facc3638ce6989d9b3efcf6dc05f9b7f87944b))
* Method Call ([f598610](https://github.com/WebFiori/framework/commit/f598610d039dd5b7f505e714de8c92bdb631d119))
* Middleware Discovery ([91aaf4b](https://github.com/WebFiori/framework/commit/91aaf4b88dbbf043311fc9f67dcb8fd3797d1176)), closes [#319](https://github.com/WebFiori/framework/issues/319)
* Migrations Command ([ce54748](https://github.com/WebFiori/framework/commit/ce5474803a26352b75eaea66604b630378b98aa9))
* **migrations:** close database connection after command execution ([e271833](https://github.com/WebFiori/framework/commit/e2718335af8713feeb3537d6b0db3a3c0fc6ba22))
* Multiple Fixes ([bd09605](https://github.com/WebFiori/framework/commit/bd09605110e17c2516200f78040bbcf1aca94a35))
* Namespaces ([e51d354](https://github.com/WebFiori/framework/commit/e51d354a2be0843f55e70ced529c45341988ba2e))
* off-by-one in middleware after() and afterSend() loops ([1faf4a1](https://github.com/WebFiori/framework/commit/1faf4a1c7c90eb40d343ae668dda98577fe4c329)), closes [#299](https://github.com/WebFiori/framework/issues/299)
* Proper Fix for The Issue ([3bb8970](https://github.com/WebFiori/framework/commit/3bb8970280e807f5f0b9a73064dc5c6a1e9f1f1d))
* References to Classes ([a140646](https://github.com/WebFiori/framework/commit/a1406462197a32a8bd16a6d12168c8c2a4fda016))
* remove exit from middleware, add pipeline halt in Router ([b8845dd](https://github.com/WebFiori/framework/commit/b8845dd033e9bdab294ca4a86b2a8e37da88e689))
* remove redundant conditional in DatabaseSessionStorage constructor ([04285de](https://github.com/WebFiori/framework/commit/04285def51f8b38eb559df776d424ad2042ad1a6))
* remove silent try-catch in StartSessionMiddleware::after() ([9c5c3b6](https://github.com/WebFiori/framework/commit/9c5c3b6a13c1d00dce57b044f95b3c8592f571d7)), closes [#298](https://github.com/WebFiori/framework/issues/298)
* Request Method not Allowed ([868e123](https://github.com/WebFiori/framework/commit/868e123f2d8d75e12b2d9160d9425ddaa105ffc2))
* **router:** resolve middleware by class reference in addMiddleware() ([6dc18da](https://github.com/WebFiori/framework/commit/6dc18daefee339e14c56e3de954302f6e38f1ef9)), closes [#318](https://github.com/WebFiori/framework/issues/318)
* **routing:** preserve query string on redirect and prevent crash on root sub-routes ([007c799](https://github.com/WebFiori/framework/commit/007c799cf2511b96d251be223fa9fca9104482c9))
* **security:** replace broken session encryption with AES-256-GCM ([577a0e3](https://github.com/WebFiori/framework/commit/577a0e3d67b46938e118f31abaa6bbcfaf2b1ef3))
* **session:** add probabilistic GC to prevent app hang with large session file counts ([a427bc8](https://github.com/WebFiori/framework/commit/a427bc89c79f08a43df950e473df67a3303fa14c))
* Show Framework Logo ([e47f1a5](https://github.com/WebFiori/framework/commit/e47f1a5d70b5d5982ff87247de3feb98ce75d558))
* Tasks Names Check ([3c80893](https://github.com/WebFiori/framework/commit/3c80893d4c793e330f571205a702b853540d9a36))
* Test Cases ([f78c6d5](https://github.com/WebFiori/framework/commit/f78c6d5a2b9fa10baa98ce9c229295d95f5b0fef))
* Test Classes ([cd68b49](https://github.com/WebFiori/framework/commit/cd68b49acbc7dab3d8abc25fa3df21b129d19bf4))
* **test:** remove deprecated setAccessible() calls for PHP 8.5 compat ([45a04bf](https://github.com/WebFiori/framework/commit/45a04bfa6f1c4ae2bf19e59f4e01ec56f3aae980))
* **test:** replace PDO with framework Database class for connection checks ([0ba52b0](https://github.com/WebFiori/framework/commit/0ba52b0d2526cbe662fb8a33578fb99b370963a9))
* **test:** resolve all baseline test failures ([6cb7f92](https://github.com/WebFiori/framework/commit/6cb7f92c51868c741fba9746aa5bb7ac1ebb88b4))
* **tests:** reset connection pool in CLITestCase tearDown ([cb813cb](https://github.com/WebFiori/framework/commit/cb813cb45c164379a50200afb41c3e8a18f3b273))
* **test:** update HelpCommandTest for queue commands ([a138a26](https://github.com/WebFiori/framework/commit/a138a263faaaa7bbf0d5effac5b3556c754afad4))
* **test:** use 127.0.0.1 instead of localhost for MySQL in CI ([231df17](https://github.com/WebFiori/framework/commit/231df1715234ce15697e1615ccfa5dd6b72f2e9b))
* **test:** use MYSQL_PORT env var with default 3306 for CI compatibility ([56b9e3e](https://github.com/WebFiori/framework/commit/56b9e3e71cf73921b374563e50090755a9fb2655))
* Theme Creation ([6864c23](https://github.com/WebFiori/framework/commit/6864c236e7174113a4b4dc26e8289ed2645bb278))
* Theme Resources Creation ([e9f1025](https://github.com/WebFiori/framework/commit/e9f10258f4433982e922cac494dec69b06f34e8b))
* use $response instance instead of static Response::addHeader() in StartSessionMiddleware ([907984c](https://github.com/WebFiori/framework/commit/907984c70f29694cccf207c9e60985709fb8aa23)), closes [#297](https://github.com/WebFiori/framework/issues/297)
* use underscores in session schema column keys for MSSQL compatibility ([4aced29](https://github.com/WebFiori/framework/commit/4aced29de6d7df73dafd8fd05c4e0b1b7367b8aa))
* Writing Classes ([2fcb0c5](https://github.com/WebFiori/framework/commit/2fcb0c5993d7eb47c270d8113f58a2cf13f76046))


### Miscellaneous Chores

* add CLI command files to coverage source config ([ddbed79](https://github.com/WebFiori/framework/commit/ddbed7905e62f545184b677b91483e30f1683f78))
* add RBAC classes to coverage source config ([c481144](https://github.com/WebFiori/framework/commit/c48114419865c37c28b426bbcfffa8eb14aac1e0))
* Added `.gitkeep` ([ec80899](https://github.com/WebFiori/framework/commit/ec80899b72ae0babab1589a8b0fd48c3042817f9))
* Added CS Fixer ([4f87f0d](https://github.com/WebFiori/framework/commit/4f87f0d07e47e58c2dfa831eb41b7a202f079c88))
* Added Missing Libs ([7eb2c76](https://github.com/WebFiori/framework/commit/7eb2c76c43293d3eebd13724da6fd9f9921d7591))
* bump version to v3.0.0-RC1 (2026-04-30) ([a9d4117](https://github.com/WebFiori/framework/commit/a9d411757e68aaa91d62be707733e236a2312f82))
* CS Fixer ([e281dfa](https://github.com/WebFiori/framework/commit/e281dfa973b9aee8730e9ce428d337ad0687bd6a))
* **dev:** release 3.0.0-Beta.26 ([d3d77a4](https://github.com/WebFiori/framework/commit/d3d77a42c6cea7578929ca09dee6e0459f18aefd))
* exclude boot classes from coverage (App, AppBootstrapper, ClassLoader) ([02ecabc](https://github.com/WebFiori/framework/commit/02ecabc31b879204fa4473b1d68f6f584f74aadb))
* exclude Writers and CLI test infrastructure from coverage ([29d7bc4](https://github.com/WebFiori/framework/commit/29d7bc4ec4151ef80758b623bd873dd1af08032d))
* Fix Storage Dir Name ([fcfbc85](https://github.com/WebFiori/framework/commit/fcfbc85e28dc4b69f01e2e27a11376dde95ce74d))
* **main:** release 3.0.0-Beta.29 ([bd05e13](https://github.com/WebFiori/framework/commit/bd05e130fe5a964fdea1f2f29a08cf4100ba8c9a))
* **main:** release 3.0.0-Beta.30 ([bd9a3bd](https://github.com/WebFiori/framework/commit/bd9a3bd297eab02b71cefc359bb4ceffba00de61))
* **main:** release 3.0.0-beta.31 ([f08d491](https://github.com/WebFiori/framework/commit/f08d491c2803b16ecd64486596ccc5727d37a1ce))
* **main:** release 3.0.0-RC.2 ([9ee2a4a](https://github.com/WebFiori/framework/commit/9ee2a4ac10a01021e45d5ede5aa594e71a4bf2a4))
* **main:** release 3.0.0-RC.3 ([9489119](https://github.com/WebFiori/framework/commit/9489119f68faa58bfeb713bb4e1d4ef1d70a31db))
* **main:** release 3.0.0-RC.4 ([8ad793f](https://github.com/WebFiori/framework/commit/8ad793fa41e332e635dc938916794b123a822480))
* **main:** release 3.0.0-RC.5 ([4960a15](https://github.com/WebFiori/framework/commit/4960a156371d187c77b9fb0d0be2daa58d2f5aa2))
* **main:** release 3.0.0-RC.5 ([554315c](https://github.com/WebFiori/framework/commit/554315c77b41830dd9c467cc6c5fcbe6d64dab81))
* **main:** release 3.0.0-RC0 ([8e3f0fe](https://github.com/WebFiori/framework/commit/8e3f0fe59fcbce62b386309726b3afb8e6581b1c))
* **main:** release 3.0.0-RC1 ([b46916d](https://github.com/WebFiori/framework/commit/b46916d17527736bc801f04ae8c25ec37c379f1d))
* Merge pull request [#266](https://github.com/WebFiori/framework/issues/266) from WebFiori/dev ([19fc94a](https://github.com/WebFiori/framework/commit/19fc94a9166ecafb2572e0926f9992b93a170341))
* Merge pull request [#268](https://github.com/WebFiori/framework/issues/268) from WebFiori/dev ([fb1e6a3](https://github.com/WebFiori/framework/commit/fb1e6a3bb3d5b69642641fd323e82785f28c72f9))
* Merge pull request [#269](https://github.com/WebFiori/framework/issues/269) from WebFiori/dev ([8ffb523](https://github.com/WebFiori/framework/commit/8ffb523559ab609095da446b2d592607dac1f20e))
* Merge pull request [#271](https://github.com/WebFiori/framework/issues/271) from WebFiori/dev ([6bf9c26](https://github.com/WebFiori/framework/commit/6bf9c26ab448e60bcdfee5f6f91b2cdc97304006))
* Merge pull request [#273](https://github.com/WebFiori/framework/issues/273) from WebFiori/dev ([21b650e](https://github.com/WebFiori/framework/commit/21b650e81178cd6ae3325bf01e1a29a4e6e8d803))
* Merge pull request [#276](https://github.com/WebFiori/framework/issues/276) from WebFiori/dev ([785add2](https://github.com/WebFiori/framework/commit/785add28805104ddbdf175a6c8a3b656fafa61a9))
* Merge pull request [#277](https://github.com/WebFiori/framework/issues/277) from WebFiori/refactor-create ([9c06b94](https://github.com/WebFiori/framework/commit/9c06b9430a6ac45348dc9d0b644c9e34489f543b))
* Merge pull request [#281](https://github.com/WebFiori/framework/issues/281) from WebFiori/dev ([fc52e00](https://github.com/WebFiori/framework/commit/fc52e00e36941ba394f000263c1cc0aabd899119))
* Merge pull request [#282](https://github.com/WebFiori/framework/issues/282) from WebFiori/dev ([a36adc0](https://github.com/WebFiori/framework/commit/a36adc097331f9e0737d66e8aa264817d317a086))
* Merge pull request [#295](https://github.com/WebFiori/framework/issues/295) from WebFiori/dev ([317ea3b](https://github.com/WebFiori/framework/commit/317ea3bb2a0d59b0d800428eb81be62cc89e19e3))
* Merge pull request [#312](https://github.com/WebFiori/framework/issues/312) from WebFiori/dev ([a629646](https://github.com/WebFiori/framework/commit/a629646133f2fc756523f6560e6f8af9d190afd9))
* Merge pull request [#315](https://github.com/WebFiori/framework/issues/315) from WebFiori/dev ([a897a10](https://github.com/WebFiori/framework/commit/a897a10122fd26eb59f79bdffb4715c42883bb09))
* Merge pull request [#316](https://github.com/WebFiori/framework/issues/316) from WebFiori/dev ([2ee892a](https://github.com/WebFiori/framework/commit/2ee892a2e7d0da0f80160d592761d253ac4bd703))
* Merge pull request [#324](https://github.com/WebFiori/framework/issues/324) from WebFiori/dev ([53dd434](https://github.com/WebFiori/framework/commit/53dd434fa34cda6676b99b7cbe7ea3550243f2f6))
* Merge pull request [#327](https://github.com/WebFiori/framework/issues/327) from WebFiori/dev ([9f2efb0](https://github.com/WebFiori/framework/commit/9f2efb0cb72af2baf355ee8e84da01b5a80d6864))
* Merge pull request [#329](https://github.com/WebFiori/framework/issues/329) from WebFiori/dev ([74dd04e](https://github.com/WebFiori/framework/commit/74dd04eececd66ee8d203c3dc36eaba45ea6c7e6))
* Pump-Up Database to V1.2 ([f8af600](https://github.com/WebFiori/framework/commit/f8af600f2fd2e70f3a34f2d0b8614021a84db79f))
* register AccessManager in DI container ([3ecb1f4](https://github.com/WebFiori/framework/commit/3ecb1f4de059f017bfc7562409886bf4da548ddc))
* release v3.0.0-Beta.25 ([7609472](https://github.com/WebFiori/framework/commit/76094722f597e943bf159b57368af5650d265af0))
* release v3.0.0-Beta.26 ([ab08f87](https://github.com/WebFiori/framework/commit/ab08f870c53cc8fc47212b579e077e8eef2fdc65))
* release v3.0.0-Beta.28 ([9ae9ba0](https://github.com/WebFiori/framework/commit/9ae9ba0d268d105e5e9129486ff487539918076e))
* release v3.0.0-RC0 ([b92e3da](https://github.com/WebFiori/framework/commit/b92e3dafb4da9f7e33415704c20732e3a005cb68))
* remove cov.xml and add to gitignore ([a7d3f1e](https://github.com/WebFiori/framework/commit/a7d3f1ed7c2201c65801eb7b2d6cc7a0bd60c6c5))
* Remove Funding Info ([29a9e00](https://github.com/WebFiori/framework/commit/29a9e00c2a498ba912d916a74b00e9a18408fc87))
* Remove Non-Needed Files ([3e64be3](https://github.com/WebFiori/framework/commit/3e64be3523a86ddfc4bd87549963f826d31cdfd6))
* Rename Folders ([ff3f35e](https://github.com/WebFiori/framework/commit/ff3f35e0707db456df5d2f24a2f42037220789a3))
* Rename Folders ([3afeaec](https://github.com/WebFiori/framework/commit/3afeaec247751d780771a0ee5e2ced586b5a180d))
* Run CS Fixer ([94775c5](https://github.com/WebFiori/framework/commit/94775c56e8cc5394767fd530fd7563a481960e02))
* Update issue templates ([e6a10a8](https://github.com/WebFiori/framework/commit/e6a10a85fddec748a82c6e033574dea434e871b8))
* Update Sonar Config ([7d230c9](https://github.com/WebFiori/framework/commit/7d230c97f58fc17cd02f723fa6ac361e7a2072ba))
* Updated Core Libraries ([641c4cf](https://github.com/WebFiori/framework/commit/641c4cf1b134f6ccdcb89b68a37617d7306b8da2))
* Updated Database Library ([a901134](https://github.com/WebFiori/framework/commit/a901134f3eb6f9b6844ed0210ff95f5683915f2a))
* Updated Database Library ([7f853fd](https://github.com/WebFiori/framework/commit/7f853fd25fd9f0b1211fb18fc807a2436f403946))
* Updated Database to v1.0.0 ([c35b109](https://github.com/WebFiori/framework/commit/c35b109d292df38b32965ceed0bd7e0a90c8cc08))
* Updated Dependencies ([ff05d95](https://github.com/WebFiori/framework/commit/ff05d95b8923ee631649c4610bf7dd348dcd4869))
* Updated Dependencies ([6936c18](https://github.com/WebFiori/framework/commit/6936c18cd1895df3ba101aaacfe4e599d39d59c4))
* Updated Framework Version ([40e135a](https://github.com/WebFiori/framework/commit/40e135ad5b348606c414402a0a26959555a2fb60))
* Updated Framework Version ([3d719ea](https://github.com/WebFiori/framework/commit/3d719ea32aea00d973dbb61e5ddf98194a5e7e0f))
* Updated Framework Version ([5ed85f3](https://github.com/WebFiori/framework/commit/5ed85f3de2f0d699b32e00dea2bfa69ac890a4d0))
* Updated Framework Version ([ddaeca0](https://github.com/WebFiori/framework/commit/ddaeca02a7a192b49340fa15b4bd88b3d1f2dfab))
* Updated Framework Verstion ([b5913c0](https://github.com/WebFiori/framework/commit/b5913c042b3787bb44acc0cc216e990b80f8f7b3))
* Updated HTTP to v4 ([ab525fc](https://github.com/WebFiori/framework/commit/ab525fc9a37db07ae454b53e961cddba628b82e3))
* Updated Index ([0b128ec](https://github.com/WebFiori/framework/commit/0b128ec53339304b351df9d1953e192dc233a24a))
* Updated Version ([be6d69d](https://github.com/WebFiori/framework/commit/be6d69dc0d24273d91a58d74eb97d2847ec4beb4))
* Updated Version ([2fac27c](https://github.com/WebFiori/framework/commit/2fac27cce7c2eb10a6b916d24219ed9a88ea0e1c))
* Updated Version Number ([5286ff7](https://github.com/WebFiori/framework/commit/5286ff7c74ce31faa736d4be3aee3099e3edf79a))
* Updated Version Number ([c2bac79](https://github.com/WebFiori/framework/commit/c2bac791aa6aca1bd9e8742c017d75e8574fd38d))
* Updated Verstion ([aeea85e](https://github.com/WebFiori/framework/commit/aeea85e73c3118cbae77756b3899bfb1669d46d3))
* Updated Verstion ([3a45081](https://github.com/WebFiori/framework/commit/3a4508102b20fd13b15eda604fd990046943c1dd))
* Workflow Name Correction ([1b72dbb](https://github.com/WebFiori/framework/commit/1b72dbb077461c124e4b9d1ad5ae13ce8c1d5709))


### Documentation

* Enhanced README ([0bf925c](https://github.com/WebFiori/framework/commit/0bf925c23596a6a1dae4c972994a564510f0065e))
* Updated README ([9131fcf](https://github.com/WebFiori/framework/commit/9131fcf59acfdada0867c9ef13bb7e380892bb5e))


### Code Refactoring

* remove scheduler WebUI and WebServices from core ([c0a2df4](https://github.com/WebFiori/framework/commit/c0a2df4d6f987454c60f24cee6f3261484149985))
* remove Util.php — inline remaining usages ([9a1c96a](https://github.com/WebFiori/framework/commit/9a1c96a7d5eac9f4846cddafe2b96319bc6a2757)), closes [#338](https://github.com/WebFiori/framework/issues/338)
* unify database session schema for all 3 supported engines ([38ba2a6](https://github.com/WebFiori/framework/commit/38ba2a66d468533056b6c7a2d8ead77adc71ef70))

## [3.0.0-RC.5](https://github.com/WebFiori/framework/compare/v3.0.0-RC.5...v3.0.0-RC.5) (2026-05-31)


### Miscellaneous Chores

* Updated Verstion ([aeea85e](https://github.com/WebFiori/framework/commit/aeea85e73c3118cbae77756b3899bfb1669d46d3))


### Documentation

* Updated README ([9131fcf](https://github.com/WebFiori/framework/commit/9131fcf59acfdada0867c9ef13bb7e380892bb5e))

## [3.0.0-RC.5](https://github.com/WebFiori/framework/compare/v3.0.0-RC.4...v3.0.0-RC.5) (2026-05-31)


### ⚠ BREAKING CHANGES

* Scheduler web UI and APIs removed. Use CLI commands (php webfiori scheduler) for task management instead.
* WebFiori\Framework\Util class has been removed. All methods were either unused, duplicated by existing framework methods, or trivially inlineable.
* Removed MySQLSessionsTable, MSSQLSessionsTable, MySQLSessionDataTable, and MSSQLSessionDataTable classes. Use
* **session:** SessionStorage::gc() signature changed from gc() to gc(string $olderThan, int $maxCount = 0). Custom storage implementations must update their gc() method signature.

### Features

* add CORS middleware ([75d4048](https://github.com/WebFiori/framework/commit/75d404817084838521af79c4ca93f137b4e93402)), closes [#341](https://github.com/WebFiori/framework/issues/341)
* add CSRF protection middleware ([9171c38](https://github.com/WebFiori/framework/commit/9171c38e1672651ea62682186e2a39cdea96b1b6)), closes [#333](https://github.com/WebFiori/framework/issues/333)
* add dependency-based middleware ordering ([cc8c201](https://github.com/WebFiori/framework/commit/cc8c201772f76df16c0bae25a1758a9b81f824fa)), closes [#334](https://github.com/WebFiori/framework/issues/334)
* add FileAccessStorage and DatabaseAccessStorage for RBAC ([cc55249](https://github.com/WebFiori/framework/commit/cc5524909beed03d9748d9dab8bb74bc1240de1c))
* add health check endpoint with auto-discovery ([2b1701d](https://github.com/WebFiori/framework/commit/2b1701d3145e0d5f5a82b995555ab27298aeb7cd)), closes [#339](https://github.com/WebFiori/framework/issues/339)
* add HTTP response caching middleware (ETag / Cache-Control) ([ae9b1ad](https://github.com/WebFiori/framework/commit/ae9b1ad6844562bdcd02722f046d328755772f75)), closes [#352](https://github.com/WebFiori/framework/issues/352)
* add maintenance mode (down/up commands) ([9d5202c](https://github.com/WebFiori/framework/commit/9d5202c24cf493685dbda545ca5318da5ac012f9)), closes [#351](https://github.com/WebFiori/framework/issues/351)
* add rate limiting middleware ([8db4e79](https://github.com/WebFiori/framework/commit/8db4e79873a7decba506d837a160e706b5b448c9)), closes [#335](https://github.com/WebFiori/framework/issues/335)
* add RBAC/ABAC system with backward compatibility ([82b43ba](https://github.com/WebFiori/framework/commit/82b43ba4b8ee6b2a604fdaa7838b48f5215e69b3))
* allow middleware instances in route definitions ([76a3dc6](https://github.com/WebFiori/framework/commit/76a3dc6a36447d339a3680a2144aea06f2d5b562)), closes [#340](https://github.com/WebFiori/framework/issues/340)
* integrate event system into framework ([d1bed19](https://github.com/WebFiori/framework/commit/d1bed19a80d8942ca56f4811c817e2790cf073e7)), closes [#347](https://github.com/WebFiori/framework/issues/347)
* integrate queue system into framework ([7eff48d](https://github.com/WebFiori/framework/commit/7eff48ddacdd26141b2920f0ddbfe477eb235d1f)), closes [#349](https://github.com/WebFiori/framework/issues/349)
* integrate webfiori/container into framework ([2b07a02](https://github.com/WebFiori/framework/commit/2b07a027627a2ff8e46319b82407977c28923dbf)), closes [#345](https://github.com/WebFiori/framework/issues/345)
* integrate webfiori/log into framework ([c596620](https://github.com/WebFiori/framework/commit/c5966208e29b542624471073f5dbf2fcf4a23205)), closes [#336](https://github.com/WebFiori/framework/issues/336)


### Bug Fixes

* **ci:** suppress abstract class warning and show skip reasons in CI ([50b1d88](https://github.com/WebFiori/framework/commit/50b1d887bdf283d8915cb851c0df9d04874e2e9a))
* remove exit from middleware, add pipeline halt in Router ([b8845dd](https://github.com/WebFiori/framework/commit/b8845dd033e9bdab294ca4a86b2a8e37da88e689))
* remove redundant conditional in DatabaseSessionStorage constructor ([04285de](https://github.com/WebFiori/framework/commit/04285def51f8b38eb559df776d424ad2042ad1a6))
* **security:** replace broken session encryption with AES-256-GCM ([577a0e3](https://github.com/WebFiori/framework/commit/577a0e3d67b46938e118f31abaa6bbcfaf2b1ef3))
* **session:** add probabilistic GC to prevent app hang with large session file counts ([a427bc8](https://github.com/WebFiori/framework/commit/a427bc89c79f08a43df950e473df67a3303fa14c))
* **test:** remove deprecated setAccessible() calls for PHP 8.5 compat ([45a04bf](https://github.com/WebFiori/framework/commit/45a04bfa6f1c4ae2bf19e59f4e01ec56f3aae980))
* **test:** replace PDO with framework Database class for connection checks ([0ba52b0](https://github.com/WebFiori/framework/commit/0ba52b0d2526cbe662fb8a33578fb99b370963a9))
* **test:** resolve all baseline test failures ([6cb7f92](https://github.com/WebFiori/framework/commit/6cb7f92c51868c741fba9746aa5bb7ac1ebb88b4))
* **test:** update HelpCommandTest for queue commands ([a138a26](https://github.com/WebFiori/framework/commit/a138a263faaaa7bbf0d5effac5b3556c754afad4))
* **test:** use 127.0.0.1 instead of localhost for MySQL in CI ([231df17](https://github.com/WebFiori/framework/commit/231df1715234ce15697e1615ccfa5dd6b72f2e9b))
* **test:** use MYSQL_PORT env var with default 3306 for CI compatibility ([56b9e3e](https://github.com/WebFiori/framework/commit/56b9e3e71cf73921b374563e50090755a9fb2655))
* use underscores in session schema column keys for MSSQL compatibility ([4aced29](https://github.com/WebFiori/framework/commit/4aced29de6d7df73dafd8fd05c4e0b1b7367b8aa))


### Miscellaneous Chores

* add CLI command files to coverage source config ([ddbed79](https://github.com/WebFiori/framework/commit/ddbed7905e62f545184b677b91483e30f1683f78))
* add RBAC classes to coverage source config ([c481144](https://github.com/WebFiori/framework/commit/c48114419865c37c28b426bbcfffa8eb14aac1e0))
* exclude boot classes from coverage (App, AppBootstrapper, ClassLoader) ([02ecabc](https://github.com/WebFiori/framework/commit/02ecabc31b879204fa4473b1d68f6f584f74aadb))
* exclude Writers and CLI test infrastructure from coverage ([29d7bc4](https://github.com/WebFiori/framework/commit/29d7bc4ec4151ef80758b623bd873dd1af08032d))
* Merge pull request [#329](https://github.com/WebFiori/framework/issues/329) from WebFiori/dev ([74dd04e](https://github.com/WebFiori/framework/commit/74dd04eececd66ee8d203c3dc36eaba45ea6c7e6))
* register AccessManager in DI container ([3ecb1f4](https://github.com/WebFiori/framework/commit/3ecb1f4de059f017bfc7562409886bf4da548ddc))
* remove cov.xml and add to gitignore ([a7d3f1e](https://github.com/WebFiori/framework/commit/a7d3f1ed7c2201c65801eb7b2d6cc7a0bd60c6c5))
* Updated Version Number ([5286ff7](https://github.com/WebFiori/framework/commit/5286ff7c74ce31faa736d4be3aee3099e3edf79a))
* Updated Verstion ([3a45081](https://github.com/WebFiori/framework/commit/3a4508102b20fd13b15eda604fd990046943c1dd))


### Code Refactoring

* remove scheduler WebUI and WebServices from core ([c0a2df4](https://github.com/WebFiori/framework/commit/c0a2df4d6f987454c60f24cee6f3261484149985))
* remove Util.php — inline remaining usages ([9a1c96a](https://github.com/WebFiori/framework/commit/9a1c96a7d5eac9f4846cddafe2b96319bc6a2757)), closes [#338](https://github.com/WebFiori/framework/issues/338)
* unify database session schema for all 3 supported engines ([38ba2a6](https://github.com/WebFiori/framework/commit/38ba2a66d468533056b6c7a2d8ead77adc71ef70))

## [3.0.0-RC.4](https://github.com/WebFiori/framework/compare/v3.0.0-RC.3...v3.0.0-RC.4) (2026-05-13)


### Features

* **cli:** add --all-connections flag and connection validation for migrations ([5a4daea](https://github.com/WebFiori/framework/commit/5a4daea0bed464f43980c273ff5d8f9b2f7967db)), closes [#326](https://github.com/WebFiori/framework/issues/326)
* **cli:** add migrations:skip command for baselining ([4cbde82](https://github.com/WebFiori/framework/commit/4cbde826774f2a533278e4d2b98f523c65fa42d7)), closes [#320](https://github.com/WebFiori/framework/issues/320)


### Miscellaneous Chores

* Merge pull request [#327](https://github.com/WebFiori/framework/issues/327) from WebFiori/dev ([9f2efb0](https://github.com/WebFiori/framework/commit/9f2efb0cb72af2baf355ee8e84da01b5a80d6864))

## [3.0.0-RC.3](https://github.com/WebFiori/framework/compare/v3.0.0-RC.2...v3.0.0-RC.3) (2026-05-11)


### Features

* **migrations:** enable recursive discovery of migrations in subdirectories ([b855c18](https://github.com/WebFiori/framework/commit/b855c18ce8ef134efefa18e9984986efa3704fe0)), closes [#317](https://github.com/WebFiori/framework/issues/317)


### Bug Fixes

* Middleware Discovery ([91aaf4b](https://github.com/WebFiori/framework/commit/91aaf4b88dbbf043311fc9f67dcb8fd3797d1176)), closes [#319](https://github.com/WebFiori/framework/issues/319)
* **migrations:** close database connection after command execution ([e271833](https://github.com/WebFiori/framework/commit/e2718335af8713feeb3537d6b0db3a3c0fc6ba22))
* **router:** resolve middleware by class reference in addMiddleware() ([6dc18da](https://github.com/WebFiori/framework/commit/6dc18daefee339e14c56e3de954302f6e38f1ef9)), closes [#318](https://github.com/WebFiori/framework/issues/318)
* **tests:** reset connection pool in CLITestCase tearDown ([cb813cb](https://github.com/WebFiori/framework/commit/cb813cb45c164379a50200afb41c3e8a18f3b273))


### Miscellaneous Chores

* Merge pull request [#324](https://github.com/WebFiori/framework/issues/324) from WebFiori/dev ([53dd434](https://github.com/WebFiori/framework/commit/53dd434fa34cda6676b99b7cbe7ea3550243f2f6))
* Updated Database Library ([a901134](https://github.com/WebFiori/framework/commit/a901134f3eb6f9b6844ed0210ff95f5683915f2a))
* Updated Version ([be6d69d](https://github.com/WebFiori/framework/commit/be6d69dc0d24273d91a58d74eb97d2847ec4beb4))

## [3.0.0-RC.2](https://github.com/WebFiori/framework/compare/v3.0.0-RC1...v3.0.0-RC.2) (2026-05-04)


### Bug Fixes

* Auto-Reg With RegEx ([1d99fb8](https://github.com/WebFiori/framework/commit/1d99fb815adfccad978d4210cf5c6535ae858657))
* Auto-Register on Attributes ([52898f9](https://github.com/WebFiori/framework/commit/52898f9def1d0ad92670e6ab2b18420c2f07a7aa)), closes [#313](https://github.com/WebFiori/framework/issues/313)


### Miscellaneous Chores

* Merge pull request [#315](https://github.com/WebFiori/framework/issues/315) from WebFiori/dev ([a897a10](https://github.com/WebFiori/framework/commit/a897a10122fd26eb59f79bdffb4715c42883bb09))
* Merge pull request [#316](https://github.com/WebFiori/framework/issues/316) from WebFiori/dev ([2ee892a](https://github.com/WebFiori/framework/commit/2ee892a2e7d0da0f80160d592761d253ac4bd703))
* Updated Framework Version ([40e135a](https://github.com/WebFiori/framework/commit/40e135ad5b348606c414402a0a26959555a2fb60))

## [3.0.0-RC1](https://github.com/WebFiori/framework/compare/v3.0.0-RC0...v3.0.0-RC1) (2026-04-29)


### ⚠ BREAKING CHANGES

* **deps:** Application code using Cache::get(), Cache::set(),

### Features

* **deps:** upgrade webfiori/cache from v2 to v3 ([c24817d](https://github.com/WebFiori/framework/commit/c24817d4f9794cca5a0f32b4b64c096dde565f9e)), closes [#301](https://github.com/WebFiori/framework/issues/301)
* **scheduler:** add scheduler:daemon command with time-limited execution ([3d3cb1d](https://github.com/WebFiori/framework/commit/3d3cb1d6ddb3aa35828935a34650b67744375391)), closes [#300](https://github.com/WebFiori/framework/issues/300)


### Bug Fixes

* correct parent constructor call and static method usage in ExtendedWebServicesManager ([b110a38](https://github.com/WebFiori/framework/commit/b110a38fd8cae51b2596fa02180e21e28e03acc0)), closes [#296](https://github.com/WebFiori/framework/issues/296)
* off-by-one in middleware after() and afterSend() loops ([1faf4a1](https://github.com/WebFiori/framework/commit/1faf4a1c7c90eb40d343ae668dda98577fe4c329)), closes [#299](https://github.com/WebFiori/framework/issues/299)
* Proper Fix for The Issue ([3bb8970](https://github.com/WebFiori/framework/commit/3bb8970280e807f5f0b9a73064dc5c6a1e9f1f1d))
* remove silent try-catch in StartSessionMiddleware::after() ([9c5c3b6](https://github.com/WebFiori/framework/commit/9c5c3b6a13c1d00dce57b044f95b3c8592f571d7)), closes [#298](https://github.com/WebFiori/framework/issues/298)
* **routing:** preserve query string on redirect and prevent crash on root sub-routes ([007c799](https://github.com/WebFiori/framework/commit/007c799cf2511b96d251be223fa9fca9104482c9))
* use $response instance instead of static Response::addHeader() in StartSessionMiddleware ([907984c](https://github.com/WebFiori/framework/commit/907984c70f29694cccf207c9e60985709fb8aa23)), closes [#297](https://github.com/WebFiori/framework/issues/297)


### Miscellaneous Chores

* bump version to v3.0.0-RC1 (2026-04-30) ([a9d4117](https://github.com/WebFiori/framework/commit/a9d411757e68aaa91d62be707733e236a2312f82))
* Merge pull request [#312](https://github.com/WebFiori/framework/issues/312) from WebFiori/dev ([a629646](https://github.com/WebFiori/framework/commit/a629646133f2fc756523f6560e6f8af9d190afd9))

## [3.0.0-RC0](https://github.com/WebFiori/framework/compare/v3.0.0-beta.31...v3.0.0-RC0) (2026-04-08)


### Features

* (cli): Run Seeders With Migrations ([7796e93](https://github.com/WebFiori/framework/commit/7796e935b04e299d85958f5646b39a2f026ecdfc))
* Add `env:` to Class Driver ([89cd39d](https://github.com/WebFiori/framework/commit/89cd39d0f48c558a471a6351218539a8c72f30b2))
* Add `property` and `constant` ([bebe151](https://github.com/WebFiori/framework/commit/bebe151ebf59b7612b4f064c5dfddcd926e7fd82))
* Add Arguments Support in Add DB Connection Command ([8223428](https://github.com/WebFiori/framework/commit/8223428c2ed1c67fc9eeb6828ec65d66627067a7))
* Add Dependencies and Envs ([e3950b6](https://github.com/WebFiori/framework/commit/e3950b601a218abee6296bef73bb3adabd9165c6))
* Add New Method" `method` ([4d486b2](https://github.com/WebFiori/framework/commit/4d486b27446d6d6e53a0d2e54a23e09b8d6e40bc))
* Added `getCode` ([ff37c27](https://github.com/WebFiori/framework/commit/ff37c27169234495d3fe8abc1a318ca039521535))
* Added `migrations:fresh` Command ([d5f732b](https://github.com/WebFiori/framework/commit/d5f732b219375f8c795bc5ed4941d89f14f07594))
* Added Options to `AttributeTableWriter` ([1d6340c](https://github.com/WebFiori/framework/commit/1d6340c93cffc8b90ab3d3b99179ceddb335a355))
* Added Scan for Seeders in Dry Run ([a2697f8](https://github.com/WebFiori/framework/commit/a2697f837b193a54e2f82cd14935f871748facf5))
* Added Support For Args in Add SMTP Conn ([4554cca](https://github.com/WebFiori/framework/commit/4554cca7730f64cc5dc1ee781790f4c081ce3d80))
* Attributes ([9e11d2b](https://github.com/WebFiori/framework/commit/9e11d2be039a04ea2b0fa6797cd463e03b146584))
* Chaining ([d3b2407](https://github.com/WebFiori/framework/commit/d3b24077f1652396226152581f043d8bc323c15a))
* Code Reuse Helpers ([f54ed91](https://github.com/WebFiori/framework/commit/f54ed91850136e334b6092e27c797ace2cf6a63c))
* Create Attributes Table ([6f4a7fc](https://github.com/WebFiori/framework/commit/6f4a7fc315667081ca22893068a4f744003b7344))
* Create Background Task Command ([fab0c39](https://github.com/WebFiori/framework/commit/fab0c39acd3ae9f88a033e43122a7b0938880ca5))
* Create CLI Command ([3c88ecf](https://github.com/WebFiori/framework/commit/3c88ecf2b547e9e55e652800e63d8c73fbe97dcf))
* Create CRUD Resource ([78d5ecf](https://github.com/WebFiori/framework/commit/78d5ecf1986e4050ab70709f304816afabc18433))
* Create Domain Entity ([3c4944c](https://github.com/WebFiori/framework/commit/3c4944c4c4a0696784f6b1b1c7b47d4d7c38d56a))
* Create Middleware Command ([2b189be](https://github.com/WebFiori/framework/commit/2b189be7fc0404942db188cbd60d5a6be01f43e2))
* Create Migration ([989cf6d](https://github.com/WebFiori/framework/commit/989cf6df71b2b3c50256b039a8ea19957e1c6cdd))
* Create Repo ([498c214](https://github.com/WebFiori/framework/commit/498c2141fd48ed23acc79e9cf08e3bfc73265fd9))
* Create Seeder ([b3038d0](https://github.com/WebFiori/framework/commit/b3038d09b671a60957bece8c24c1bf25169a0c9f))
* Create Table Command ([6fe3be5](https://github.com/WebFiori/framework/commit/6fe3be5486c6f98ec8409a644ad15d8ec5f96cd0))
* Doc-block Builder ([0eb7760](https://github.com/WebFiori/framework/commit/0eb7760ea9107f5a004f30e2b2d0b5526e3eb9ac))
* Env Vars for Json Driver ([f28f977](https://github.com/WebFiori/framework/commit/f28f9776e7b19d91b352ef1e3fb4a1cc7a793674))
* Optional Config Var ([b34cd18](https://github.com/WebFiori/framework/commit/b34cd182ae731d9b814b3362da1332b103e08277))
* **scheduler:** Added Support for `env:` Syntax ([8240078](https://github.com/WebFiori/framework/commit/824007895f60ba0bb6c774c7ceb4dfe15dc987f6))
* Split Migrations Count from Seeders Count ([69c69d6](https://github.com/WebFiori/framework/commit/69c69d655ae0473d46a783cbeb04f97e326c90df))
* Web Services Writer ([ff39f02](https://github.com/WebFiori/framework/commit/ff39f02ddadcfcbef0bccd9d3c3dc38fec1c49eb))


### Bug Fixes

* Access Modifier ([95016a8](https://github.com/WebFiori/framework/commit/95016a88a419eeed43e7e02cce70ac3bfbe16754))
* App Directories Creation ([a8ee667](https://github.com/WebFiori/framework/commit/a8ee6672e2758e919dc7b2bfcc6e8d3718cab82f))
* Check for Empty `--class-name` ([e5f845c](https://github.com/WebFiori/framework/commit/e5f845cdeea492ba59d0069da0c06114d8badc37))
* CLI Test Case Class Fix ([ac64c01](https://github.com/WebFiori/framework/commit/ac64c0180521c036a47bfb7608baecd988edd116))
* **cli:** Deleting All Records of Changes Table ([61d2107](https://github.com/WebFiori/framework/commit/61d2107bf05aef515a92cd19df80a903bcc776a8))
* **cli:** object-to-string conversion errors ([e1a6e81](https://github.com/WebFiori/framework/commit/e1a6e810a3ac4919934a7b61b3ca68a4108c393e))
* **cli:** Run Migrations With No Tables Check ([b5e9f06](https://github.com/WebFiori/framework/commit/b5e9f067a8f0848095af373ad2eab4b5cf4a69bc))
* Fix Session Tables Creation ([f318ba4](https://github.com/WebFiori/framework/commit/f318ba4204c45d49717b7dea85f5fbda5645b070))
* Getting Requested URI ([79f47a8](https://github.com/WebFiori/framework/commit/79f47a8682a5ffd15c604b6e9138ec912c391c3a))
* Initialization Path ([192c2f0](https://github.com/WebFiori/framework/commit/192c2f02ea2da20e28ed577c6211bc17f4052b4f))
* Max/Min Values Issue ([b57b6bc](https://github.com/WebFiori/framework/commit/b57b6bc51de36c579cd9a3dd1ebc065e06be18fe))
* Method Call ([f598610](https://github.com/WebFiori/framework/commit/f598610d039dd5b7f505e714de8c92bdb631d119))
* Multiple Fixes ([bd09605](https://github.com/WebFiori/framework/commit/bd09605110e17c2516200f78040bbcf1aca94a35))
* Request Method not Allowed ([868e123](https://github.com/WebFiori/framework/commit/868e123f2d8d75e12b2d9160d9425ddaa105ffc2))


### Miscellaneous Chores

* Added `.gitkeep` ([ec80899](https://github.com/WebFiori/framework/commit/ec80899b72ae0babab1589a8b0fd48c3042817f9))
* Added CS Fixer ([4f87f0d](https://github.com/WebFiori/framework/commit/4f87f0d07e47e58c2dfa831eb41b7a202f079c88))
* CS Fixer ([e281dfa](https://github.com/WebFiori/framework/commit/e281dfa973b9aee8730e9ce428d337ad0687bd6a))
* Fix Storage Dir Name ([fcfbc85](https://github.com/WebFiori/framework/commit/fcfbc85e28dc4b69f01e2e27a11376dde95ce74d))
* Merge pull request [#273](https://github.com/WebFiori/framework/issues/273) from WebFiori/dev ([21b650e](https://github.com/WebFiori/framework/commit/21b650e81178cd6ae3325bf01e1a29a4e6e8d803))
* Merge pull request [#276](https://github.com/WebFiori/framework/issues/276) from WebFiori/dev ([785add2](https://github.com/WebFiori/framework/commit/785add28805104ddbdf175a6c8a3b656fafa61a9))
* Merge pull request [#277](https://github.com/WebFiori/framework/issues/277) from WebFiori/refactor-create ([9c06b94](https://github.com/WebFiori/framework/commit/9c06b9430a6ac45348dc9d0b644c9e34489f543b))
* Merge pull request [#281](https://github.com/WebFiori/framework/issues/281) from WebFiori/dev ([fc52e00](https://github.com/WebFiori/framework/commit/fc52e00e36941ba394f000263c1cc0aabd899119))
* Merge pull request [#282](https://github.com/WebFiori/framework/issues/282) from WebFiori/dev ([a36adc0](https://github.com/WebFiori/framework/commit/a36adc097331f9e0737d66e8aa264817d317a086))
* Merge pull request [#295](https://github.com/WebFiori/framework/issues/295) from WebFiori/dev ([317ea3b](https://github.com/WebFiori/framework/commit/317ea3bb2a0d59b0d800428eb81be62cc89e19e3))
* Pump-Up Database to V1.2 ([f8af600](https://github.com/WebFiori/framework/commit/f8af600f2fd2e70f3a34f2d0b8614021a84db79f))
* release v3.0.0-RC0 ([b92e3da](https://github.com/WebFiori/framework/commit/b92e3dafb4da9f7e33415704c20732e3a005cb68))
* Remove Funding Info ([29a9e00](https://github.com/WebFiori/framework/commit/29a9e00c2a498ba912d916a74b00e9a18408fc87))
* Remove Non-Needed Files ([3e64be3](https://github.com/WebFiori/framework/commit/3e64be3523a86ddfc4bd87549963f826d31cdfd6))
* Rename Folders ([ff3f35e](https://github.com/WebFiori/framework/commit/ff3f35e0707db456df5d2f24a2f42037220789a3))
* Rename Folders ([3afeaec](https://github.com/WebFiori/framework/commit/3afeaec247751d780771a0ee5e2ced586b5a180d))
* Run CS Fixer ([94775c5](https://github.com/WebFiori/framework/commit/94775c56e8cc5394767fd530fd7563a481960e02))
* Update issue templates ([e6a10a8](https://github.com/WebFiori/framework/commit/e6a10a85fddec748a82c6e033574dea434e871b8))
* Update Sonar Config ([7d230c9](https://github.com/WebFiori/framework/commit/7d230c97f58fc17cd02f723fa6ac361e7a2072ba))
* Updated Core Libraries ([641c4cf](https://github.com/WebFiori/framework/commit/641c4cf1b134f6ccdcb89b68a37617d7306b8da2))
* Updated Framework Verstion ([b5913c0](https://github.com/WebFiori/framework/commit/b5913c042b3787bb44acc0cc216e990b80f8f7b3))
* Workflow Name Correction ([1b72dbb](https://github.com/WebFiori/framework/commit/1b72dbb077461c124e4b9d1ad5ae13ce8c1d5709))

## [3.0.0-beta.31](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.30...v3.0.0-beta.31) (2025-10-27)


### Bug Fixes

* Bug in Finding Class Loader Path ([6831a96](https://github.com/WebFiori/framework/commit/6831a96d91962480b097561b559e2ecf07af1c0b))
* Show Framework Logo ([e47f1a5](https://github.com/WebFiori/framework/commit/e47f1a5d70b5d5982ff87247de3feb98ce75d558))


### Miscellaneous Chores

* Merge pull request [#271](https://github.com/WebFiori/framework/issues/271) from WebFiori/dev ([6bf9c26](https://github.com/WebFiori/framework/commit/6bf9c26ab448e60bcdfee5f6f91b2cdc97304006))
* Updated Framework Version ([3d719ea](https://github.com/WebFiori/framework/commit/3d719ea32aea00d973dbb61e5ddf98194a5e7e0f))
* Updated Index ([0b128ec](https://github.com/WebFiori/framework/commit/0b128ec53339304b351df9d1953e192dc233a24a))

## [3.0.0-Beta.30](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.29...v3.0.0-Beta.30) (2025-10-21)


### Features

* Added a Script to Update Version ([e998c47](https://github.com/WebFiori/framework/commit/e998c476b2629b6b8307d6ad52813cdc1a63eb05))


### Bug Fixes

* Metadata ([84facc3](https://github.com/WebFiori/framework/commit/84facc3638ce6989d9b3efcf6dc05f9b7f87944b))


### Miscellaneous Chores

* Merge pull request [#269](https://github.com/WebFiori/framework/issues/269) from WebFiori/dev ([8ffb523](https://github.com/WebFiori/framework/commit/8ffb523559ab609095da446b2d592607dac1f20e))

## [3.0.0-Beta.29](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.28...v3.0.0-Beta.29) (2025-10-09)


### Bug Fixes

* API Creation ([d3c384f](https://github.com/WebFiori/framework/commit/d3c384f48f61c828cf9358cbb1675e26be25b0ee))
* Command Writer ([338e8d2](https://github.com/WebFiori/framework/commit/338e8d2d7e72e66463bb8d57a6b34f20aa38ff3b))
* Fix Test Case ([6b26e2f](https://github.com/WebFiori/framework/commit/6b26e2f023cea94eb7fdf2a665f1a721e5e190eb))
* Fix Test Cases ([1086336](https://github.com/WebFiori/framework/commit/108633622e1938699360b45d65d3565bcf611e85))
* Fix Test Cases ([0c914de](https://github.com/WebFiori/framework/commit/0c914de97f37eef1bf862c4b37f323470dfcf5ef))
* Fixes and Tests Refactoring ([5ff31ab](https://github.com/WebFiori/framework/commit/5ff31ab5dc2c33b8a9389f3bf33001a525f57a7d))
* Getting Arg Value in CLI ([c56b9bf](https://github.com/WebFiori/framework/commit/c56b9bf6f34617336ad1f750943f705d84357ba0))
* Migrations Command ([ce54748](https://github.com/WebFiori/framework/commit/ce5474803a26352b75eaea66604b630378b98aa9))
* Namespaces ([e51d354](https://github.com/WebFiori/framework/commit/e51d354a2be0843f55e70ced529c45341988ba2e))
* References to Classes ([a140646](https://github.com/WebFiori/framework/commit/a1406462197a32a8bd16a6d12168c8c2a4fda016))
* Tasks Names Check ([3c80893](https://github.com/WebFiori/framework/commit/3c80893d4c793e330f571205a702b853540d9a36))
* Test Cases ([f78c6d5](https://github.com/WebFiori/framework/commit/f78c6d5a2b9fa10baa98ce9c229295d95f5b0fef))
* Test Classes ([cd68b49](https://github.com/WebFiori/framework/commit/cd68b49acbc7dab3d8abc25fa3df21b129d19bf4))
* Theme Creation ([6864c23](https://github.com/WebFiori/framework/commit/6864c236e7174113a4b4dc26e8289ed2645bb278))
* Theme Resources Creation ([e9f1025](https://github.com/WebFiori/framework/commit/e9f10258f4433982e922cac494dec69b06f34e8b))
* Writing Classes ([2fcb0c5](https://github.com/WebFiori/framework/commit/2fcb0c5993d7eb47c270d8113f58a2cf13f76046))


### Miscellaneous Chores

* Merge pull request [#266](https://github.com/WebFiori/framework/issues/266) from WebFiori/dev ([19fc94a](https://github.com/WebFiori/framework/commit/19fc94a9166ecafb2572e0926f9992b93a170341))
* Merge pull request [#268](https://github.com/WebFiori/framework/issues/268) from WebFiori/dev ([fb1e6a3](https://github.com/WebFiori/framework/commit/fb1e6a3bb3d5b69642641fd323e82785f28c72f9))
* Updated Database Library ([7f853fd](https://github.com/WebFiori/framework/commit/7f853fd25fd9f0b1211fb18fc807a2436f403946))
* Updated Database to v1.0.0 ([c35b109](https://github.com/WebFiori/framework/commit/c35b109d292df38b32965ceed0bd7e0a90c8cc08))
* Updated Dependencies ([ff05d95](https://github.com/WebFiori/framework/commit/ff05d95b8923ee631649c4610bf7dd348dcd4869))
* Updated HTTP to v4 ([ab525fc](https://github.com/WebFiori/framework/commit/ab525fc9a37db07ae454b53e961cddba628b82e3))
* Updated Version Number ([c2bac79](https://github.com/WebFiori/framework/commit/c2bac791aa6aca1bd9e8742c017d75e8574fd38d))

## [3.0.0-Beta.26](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.26...v3.0.0-Beta.26) (2025-04-07)


### Features

* Added a Method to Load Multiple Files ([89d0363](https://github.com/WebFiori/framework/commit/89d0363bb81a32032e938da71a19ec959c48e2bf))
* Added a Way to Handle Configuration Errors ([76f1539](https://github.com/WebFiori/framework/commit/76f153933680c4ae4d7b067e8fca95273412ab2d))
* Added Ability to Enable or Disable Cache ([434fd72](https://github.com/WebFiori/framework/commit/434fd726657d7e4967681933bd718d60f68f2a76))
* Added Additional Logging Methods to Tasks Manager ([afc9b46](https://github.com/WebFiori/framework/commit/afc9b4697b58dbeb0cb7df26e9303fc1e720ecce))
* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Added Support for Loading Non-PSR-4 Compliant Classes ([a9772b4](https://github.com/WebFiori/framework/commit/a9772b49fc94b1a524e4555a18135461e1ef88ac))
* Added Support for Setting Env Vars Using `putenv()` ([2895d6f](https://github.com/WebFiori/framework/commit/2895d6fd7df6060ebae227867dba719864b3578a))
* Added Support for Writing Unit Test Classes for APIs ([baefa85](https://github.com/WebFiori/framework/commit/baefa855b76a7f42fb2ca0323888d0bc7d7d1f96))
* **autoloader:** Added a Method to Check Validity of Namespace ([e749a3a](https://github.com/WebFiori/framework/commit/e749a3aafa0d6c1a11da7d486cb68ad8b048b4b7))
* Automation of Writing Unit Test Cases for APIs ([5bab349](https://github.com/WebFiori/framework/commit/5bab349082328e668f13c5192dd5555b99201fa9))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Fully Implemented Migrations Writer ([ac42d3f](https://github.com/WebFiori/framework/commit/ac42d3fe853fa259382bc05214483ac4146c341e))
* Rollback of Migrations ([078c94f](https://github.com/WebFiori/framework/commit/078c94f98b090176b22e2354e326be7153258663))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Bug Fixes

* Add Missing Returns ([9dcd9bf](https://github.com/WebFiori/framework/commit/9dcd9bf2670116a514169abcfdd5af72d4b12d11))
* Added Check for Empty File Path ([b046fdf](https://github.com/WebFiori/framework/commit/b046fdf98768a63d882d102c1d20cc01b4f8a288))
* Added Handling Code for Session Serialization Errors ([a2c7955](https://github.com/WebFiori/framework/commit/a2c7955888483c4eb8e446c1b5bd8794331a174a))
* Added Missing Namespace ([069364a](https://github.com/WebFiori/framework/commit/069364a4566dc15f917ae0469fb8548ae5411771))
* **autoload:** Add File Name an NS ([eb4d5b9](https://github.com/WebFiori/framework/commit/eb4d5b93f6ea4dc12e5809a6fde63c9f2d4fa928))
* **autoload:** Check NS with Path ([a3d4c6e](https://github.com/WebFiori/framework/commit/a3d4c6e2e52eae4f6c421f533b7316b8562b1bf8))
* Buffer Theme Components as They Might be HTML ([d803352](https://github.com/WebFiori/framework/commit/d803352bb1d97436f242807879e665c24015845a))
* Class Path ([98c6ee6](https://github.com/WebFiori/framework/commit/98c6ee64ca4164b04c9453ef6e16685ee5b8b176))
* **cli:** Rename of Class `CommandArgument` to `Argument` ([7f67a0f](https://github.com/WebFiori/framework/commit/7f67a0f61886159261c4749955d34a4187e76cbc))
* **config:** Fix to JSON Configuration Style ([4dda36c](https://github.com/WebFiori/framework/commit/4dda36c14c8f8a77479bebb24b7b504e4bf02817))
* Correction to File Path ([df3eacf](https://github.com/WebFiori/framework/commit/df3eacfb150a43020794545f51ee1379256a46fc))
* Created Class Path ([0592ed2](https://github.com/WebFiori/framework/commit/0592ed22403d5890f03279d3dec11f35ee968946))
* Fix Assignment Issue ([34a522f](https://github.com/WebFiori/framework/commit/34a522ff53e8ad7bc8bc1287bc0c7595a4d7e254))
* Fix to `RunSQLQueryCommand` ([87dc2e3](https://github.com/WebFiori/framework/commit/87dc2e3a2dbf9f25dc81db2e9af9123aef198d4c))
* Fix to a Bug in Creating Test Case ([0e4b8e5](https://github.com/WebFiori/framework/commit/0e4b8e5ff0c307e45bd5fb3a2acbfacc82f9d373))
* Fix to Bug in Loading Themes ([ce67490](https://github.com/WebFiori/framework/commit/ce674903b6358824c61360a2ae335b8399c38309))
* Fix to Create CLI Command ([82c7a88](https://github.com/WebFiori/framework/commit/82c7a888a0a5140d1381b9855ecf2762eb52659b))
* Fix to Create Migration with Defaults ([534845d](https://github.com/WebFiori/framework/commit/534845df19c2fe7f2bec8559b77e47576c04313a))
* Fix to Initial Namespace ([6e0e08a](https://github.com/WebFiori/framework/commit/6e0e08ace2b63c2cb959a236342014962c6a3b01))
* Fix to Line Numbers in Exception Logging ([781a233](https://github.com/WebFiori/framework/commit/781a233a9e0bdb95c4d1a40b96358d608e07de0e))
* Fix to Reading Extra Connection Props ([a6c5b92](https://github.com/WebFiori/framework/commit/a6c5b9269ac6f7a354f944f0bbc9557f6a73dd1f))
* Fix to Registering Middleware ([6cc7ce1](https://github.com/WebFiori/framework/commit/6cc7ce1e39bf0aebfe0eb5ff91360e4c4ed04f05))
* Fix to Running SQL Query from File ([0c8bb61](https://github.com/WebFiori/framework/commit/0c8bb613dbdec50c06f80ee0e4d9850602d8a71b))
* Fix to Setting Middleware Name ([3a02a60](https://github.com/WebFiori/framework/commit/3a02a60d0ed3a2decf0059ce889325fc02f64893))
* Fix to Undefined Constant ([d605a5b](https://github.com/WebFiori/framework/commit/d605a5be85cd8623a2114b8c0756372c82bd7c9b))
* Fix to Uninitialized Variable ([905c3c7](https://github.com/WebFiori/framework/commit/905c3c7b8232a8f1ee6171fa309c2489b1bdd141))
* Made `init` Static ([e04233a](https://github.com/WebFiori/framework/commit/e04233a0b4b65b903d92029dcb80ec4814dd5a08))
* Remove Unused Import ([ed43960](https://github.com/WebFiori/framework/commit/ed43960b90052084b7a95a9ac182619af1244a3f))
* Show Exception on Initialization ([2341841](https://github.com/WebFiori/framework/commit/2341841f9718beb99330f8529e01600d61acfecf))
* **themes:** Fix to Problems in Loading Theme ([7a331ff](https://github.com/WebFiori/framework/commit/7a331ff9484fe13fcbd7a7c653321b3e9d233fba))
* **ui:** Fix to Bug In Web Page Initialization ([8645c2a](https://github.com/WebFiori/framework/commit/8645c2a024276c70a96b0ebc3b83480649bd09d7))
* **ui:** Fix to Load Language After Page Initialization ([38b0843](https://github.com/WebFiori/framework/commit/38b084385251e8b5bfdae2c8ade3ab0219bba046))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Cleanup ([0d5f798](https://github.com/WebFiori/framework/commit/0d5f7983426f7d766e79e6932ec47cf9ac7853dd))
* Code Quality Improvements ([80c7853](https://github.com/WebFiori/framework/commit/80c7853f737b16e605e11fd9bcf56f1ecc24223a))
* Code Quality Improvments ([f8e9ed9](https://github.com/WebFiori/framework/commit/f8e9ed98f1ac4b1c86cceff30a92ffd6107a05d2))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* **dev:** release 3.0.0-Beta.14 ([60aa746](https://github.com/WebFiori/framework/commit/60aa746bf39ccf7cbdda5bd9c24a6ed408d2732c))
* **dev:** release 3.0.0-Beta.14 ([8c3dd76](https://github.com/WebFiori/framework/commit/8c3dd7651f604414c5e5ccfd8567d907545d5513))
* **dev:** release 3.0.0-Beta.17 ([a3e5983](https://github.com/WebFiori/framework/commit/a3e598305d75f2fa87d6148520d88f4235a53253))
* **dev:** release 3.0.0-Beta.18 ([a994097](https://github.com/WebFiori/framework/commit/a994097c021b5575cab7f077f8d730736c3c1bbe))
* **dev:** release 3.0.0-Beta.19 ([e917eb5](https://github.com/WebFiori/framework/commit/e917eb5ccd18ee8223a73ec9a2ac499a281f5764))
* **dev:** release 3.0.0-Beta.19 ([5497825](https://github.com/WebFiori/framework/commit/549782509f23be3a90375debe0319b16e549a3aa))
* **dev:** release 3.0.0-Beta.20 ([7afdb92](https://github.com/WebFiori/framework/commit/7afdb92618bdbc6e11adb29b001d9a9d0a8f4809))
* **dev:** release 3.0.0-Beta.21 ([eef1270](https://github.com/WebFiori/framework/commit/eef1270f51089065f04e7f8843d9944d368a774e))
* **dev:** release 3.0.0-Beta.22 ([0e428f0](https://github.com/WebFiori/framework/commit/0e428f03ef510cb9c33bbf940c92b12ff702c8dd))
* **dev:** release 3.0.0-Beta.23 ([0f70d8b](https://github.com/WebFiori/framework/commit/0f70d8bd2bcfef80bd677d242c79be3cb23a122f))
* **dev:** release 3.0.0-Beta.24 ([d77357f](https://github.com/WebFiori/framework/commit/d77357f5b220f66e34334703c83c86c66b7fb9ae))
* **dev:** release 3.0.0-Beta.25 ([e94ea85](https://github.com/WebFiori/framework/commit/e94ea85e94e941bf670954873e06723bf26dc5f3))
* **dev:** release 3.1.0-Beta.14 ([ba5a5e3](https://github.com/WebFiori/framework/commit/ba5a5e30b4033d3f2486cbab578545aadbed67b0))
* Fix Imports ([7386f92](https://github.com/WebFiori/framework/commit/7386f9242351673588eaefe6c0de02c7e467f62a))
* Fix target Branch ([a419a3e](https://github.com/WebFiori/framework/commit/a419a3e29bb416be328b19f1489788d51ebbcd4e))
* Libraries Bump Up ([d79a44a](https://github.com/WebFiori/framework/commit/d79a44a7d2c3e062974031081c3f5dbc24812b56))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* release 3.0.0-Beta.20 ([ddcf6b0](https://github.com/WebFiori/framework/commit/ddcf6b03010a0f011112bfa789f446b1949daaae))
* release 3.0.0-Beta.20 ([c4fc053](https://github.com/WebFiori/framework/commit/c4fc053d45a5c8b24e963a625d0954c33af2c884))
* release 3.0.0-Beta.20 ([6e72830](https://github.com/WebFiori/framework/commit/6e72830f0ec1f6943d84ee3266632d5c62e02832))
* release v3.0.0-Beta.17 ([3c0c639](https://github.com/WebFiori/framework/commit/3c0c639a72f9dd08bec7a150e33af2bb18e9728a))
* release v3.0.0-Beta.18 ([5a588eb](https://github.com/WebFiori/framework/commit/5a588eb52815889a8409a07a30c4e6f0defe3269))
* release v3.0.0-Beta.19 ([e8d5314](https://github.com/WebFiori/framework/commit/e8d531433f6afada6684050013b4169b3d8d547b))
* release v3.0.0-Beta.20 ([630f512](https://github.com/WebFiori/framework/commit/630f512c6a036e111a7b146cacbd7d72dfe0da06))
* release v3.0.0-Beta.21 ([56f0bdc](https://github.com/WebFiori/framework/commit/56f0bdc7917faa97c8d3e4b73076d91213b85366))
* release v3.0.0-Beta.22 ([4271914](https://github.com/WebFiori/framework/commit/4271914182564a0c917a185010933f7f76b86f5d))
* release v3.0.0-Beta.23 ([3f74229](https://github.com/WebFiori/framework/commit/3f74229b2b38449330763f40883a6ce383eda504))
* release v3.0.0-Beta.24 ([2870002](https://github.com/WebFiori/framework/commit/28700026d50339f1b3be07f21d801594d682b7b4))
* release v3.0.0-Beta.25 ([7609472](https://github.com/WebFiori/framework/commit/76094722f597e943bf159b57368af5650d265af0))
* release v3.0.0-Beta.25 ([77671f5](https://github.com/WebFiori/framework/commit/77671f5735902aef7fd25cf1dd6c5fa601ca7eca))
* release v3.0.0-Beta.26 ([ab08f87](https://github.com/WebFiori/framework/commit/ab08f870c53cc8fc47212b579e077e8eef2fdc65))
* **release-please:** Added Additional Sections ([40dcfa4](https://github.com/WebFiori/framework/commit/40dcfa4bad0f8b42a34e0541ef558cd78f37b2ce))
* Remove Redeclaration ([f41549d](https://github.com/WebFiori/framework/commit/f41549da7a7570ec9984a53f16abf863a716e55d))
* Remove Unused Import ([4cd7cf3](https://github.com/WebFiori/framework/commit/4cd7cf313f836231c76b4533bacf7f7283589052))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Run CS Fixer ([ca8e690](https://github.com/WebFiori/framework/commit/ca8e690d7e8dcc737d4fe125ea828ec4ef146035))
* Skeleton of Database Migrations Writer ([3b53f8e](https://github.com/WebFiori/framework/commit/3b53f8e33a89667df0479d146e7902db3b8d4d90))
* Update composer.json ([08c60a8](https://github.com/WebFiori/framework/commit/08c60a878c48198f61b2149127113c359ca5f635))
* Update composer.json ([819c26d](https://github.com/WebFiori/framework/commit/819c26d8fd7f23a057a76fa923b62d0a2281721d))
* Update Libraries Versions ([46f4d56](https://github.com/WebFiori/framework/commit/46f4d56aa8bc911393ed80d4e57368472dbcdd24))
* Updated .gitattribute ([63ba6d8](https://github.com/WebFiori/framework/commit/63ba6d890b82280d87d002f8c3fcfee1493ea2ff))
* Updated .gitattributes ([3b2334c](https://github.com/WebFiori/framework/commit/3b2334ce9b29d1be3b71049b3caf759a00c84724))
* Updated App Version ([099d631](https://github.com/WebFiori/framework/commit/099d631e4c65de00fea4242cb8b5814dc6047113))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated CI Config ([a7175a4](https://github.com/WebFiori/framework/commit/a7175a4442cb6d5d4031d03cf228fc43439b504b))
* Updated Composer Config ([cf26913](https://github.com/WebFiori/framework/commit/cf2691382d6d883f2f06b25e789b9a30524758cd))
* Updated Core Framework Libraries ([9220fa4](https://github.com/WebFiori/framework/commit/9220fa4c77c668793962afc427495adcd6c8ca55))
* Updated Core Libraries ([c21a48f](https://github.com/WebFiori/framework/commit/c21a48f6586068b4cab3c223465e4b3c60849752))
* Updated Core Libraries ([fda39a9](https://github.com/WebFiori/framework/commit/fda39a9168b6e8cebda1408e0de4f0f3815845f5))
* Updated Core Libraries ([4aa9670](https://github.com/WebFiori/framework/commit/4aa96707feabd9518a788d4393442b0287f0a375))
* Updated Core Libraries Versions ([dcb1a15](https://github.com/WebFiori/framework/commit/dcb1a15882cac069cb7439101b8e096018c1cfc1))
* Updated Core Library Version ([db4d223](https://github.com/WebFiori/framework/commit/db4d223a4cb2b8bc40a45fa69e4d67b358d1f29a))
* Updated Dependences ([a160f0f](https://github.com/WebFiori/framework/commit/a160f0fcc7cb0b2570c9487090b2bcc3e0ad658e))
* Updated Dependencies ([6936c18](https://github.com/WebFiori/framework/commit/6936c18cd1895df3ba101aaacfe4e599d39d59c4))
* Updated Dependencies ([e48f333](https://github.com/WebFiori/framework/commit/e48f3336d8c0b1910e18b8baa5ea40be28c9e50d))
* Updated Dependencies ([8284dc6](https://github.com/WebFiori/framework/commit/8284dc655e8e92aafc3fb6a1bd88861254da0fe1))
* Updated Dependencies ([97bb7a2](https://github.com/WebFiori/framework/commit/97bb7a220a9c8a81fd70a4c2d80d891e4f4c7eb2))
* Updated Dependencies ([aef4319](https://github.com/WebFiori/framework/commit/aef4319b1dd10cd4f208e943e638b4b364b04cd0))
* Updated Dependencies + Framework Version ([9f5dd93](https://github.com/WebFiori/framework/commit/9f5dd9374ebf818605b6ae4acff3d5b95237d1ff))
* Updated Dependencies Version ([0d3ead5](https://github.com/WebFiori/framework/commit/0d3ead5cad177efd50e4b285222fcdbaf8beab66))
* Updated Dependencies Versions ([07252d0](https://github.com/WebFiori/framework/commit/07252d09f40af27cc04494ea7081a41fe0fe2ede))
* Updated Errors Handling Library ([5cf44a9](https://github.com/WebFiori/framework/commit/5cf44a9b5ecae3ac5ed3888c18c33e5415055703))
* Updated Framework Version ([ddaeca0](https://github.com/WebFiori/framework/commit/ddaeca02a7a192b49340fa15b4bd88b3d1f2dfab))
* Updated Framework Version ([36b0f55](https://github.com/WebFiori/framework/commit/36b0f5514329db80bae9102094e402e240987260))
* Updated Framework Version ([834782c](https://github.com/WebFiori/framework/commit/834782c8fd1ae846ffbaa72b8ee76e5fc7796f56))
* Updated Framework Version ([7f84cf6](https://github.com/WebFiori/framework/commit/7f84cf65da991a63daccc2cb0896b78b98d578c5))
* Updated Framework Version ([361e5d5](https://github.com/WebFiori/framework/commit/361e5d545a274043efceab4087d4db7769990d60))
* Updated Framework Version ([783f4be](https://github.com/WebFiori/framework/commit/783f4be57869ae93eab8c0b49fe2ede5cc7fbba8))
* Updated Framework Version ([0403027](https://github.com/WebFiori/framework/commit/0403027fc02bfbba13dd1c899ae073f43c925cd8))
* Updated Framework Version ([f7c0f7f](https://github.com/WebFiori/framework/commit/f7c0f7f0dad2900d988b3866c2a035b0b1c10e7b))
* Updated Framework Version ([d44cedc](https://github.com/WebFiori/framework/commit/d44cedc29ce9097403bdb263c279812f78d7581b))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
* Updated Framework Version ([fb91c15](https://github.com/WebFiori/framework/commit/fb91c15587fb006f096b90a2f2b01e5b9ffb7c47))
* Updated Framework Version ([a817e8f](https://github.com/WebFiori/framework/commit/a817e8feec235cec29e6c20e00732a25d1c80534))
* Updated Framework Version ([1672faf](https://github.com/WebFiori/framework/commit/1672faf3f12ef217915d5ea62d6fae9e01c9db28))
* Updated Framework Version ([8b013ae](https://github.com/WebFiori/framework/commit/8b013ae6d622823f4abd935cbda45d0a718030a1))
* Updated Framework Version ([7841fd2](https://github.com/WebFiori/framework/commit/7841fd266b92146c4eb800adc1b859c9e022dd25))
* Updated Framework Version Number ([2f8d814](https://github.com/WebFiori/framework/commit/2f8d814fd477b3c8699ada67c2c6b47a04f29b10))
* Updated Framework Version Number ([84c7857](https://github.com/WebFiori/framework/commit/84c785722512e04c106412f3612f9ef59758ed40))
* Updated Framework Version Number ([72cb62d](https://github.com/WebFiori/framework/commit/72cb62d198cef51275c282142edacb13b1a5bcd4))
* Updated Framework Version Number ([bc89447](https://github.com/WebFiori/framework/commit/bc8944771dd1bd629f65a0df9e4067ea76e141da))
* Updated Framework Version Number ([a5177bb](https://github.com/WebFiori/framework/commit/a5177bbb1de08d1cf6748ec09ec6d9e53fa20d10))
* Updated Libraries Versions ([248d1da](https://github.com/WebFiori/framework/commit/248d1dab9efffd44ac5728d005dde58470d2fef1))
* Updated Library Version ([c681042](https://github.com/WebFiori/framework/commit/c68104267d7abf876d2be93b3f7f2ea96697ca17))
* Updated Release Please Config ([1a8b4e5](https://github.com/WebFiori/framework/commit/1a8b4e55f9a5496aac47e30228613d8a24068914))
* Updated Version Number ([280f418](https://github.com/WebFiori/framework/commit/280f418df38a2eb019b6f2a5d0c1b8b8d00133d3))
* Updated Version Number ([d75c9d0](https://github.com/WebFiori/framework/commit/d75c9d0c9547d2e4ce3edbac839a1f712a9f90a4))

## [3.0.0-Beta.25](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.24...v3.0.0-Beta.25) (2025-02-04)


### Miscellaneous Chores

* release v3.0.0-Beta.25 ([77671f5](https://github.com/WebFiori/framework/commit/77671f5735902aef7fd25cf1dd6c5fa601ca7eca))
* Updated App Version ([099d631](https://github.com/WebFiori/framework/commit/099d631e4c65de00fea4242cb8b5814dc6047113))

## [3.0.0-Beta.24](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.23...v3.0.0-Beta.24) (2025-01-29)


### Bug Fixes

* Show Exception on Initialization ([2341841](https://github.com/WebFiori/framework/commit/2341841f9718beb99330f8529e01600d61acfecf))


### Miscellaneous Chores

* release v3.0.0-Beta.24 ([2870002](https://github.com/WebFiori/framework/commit/28700026d50339f1b3be07f21d801594d682b7b4))
* Update composer.json ([08c60a8](https://github.com/WebFiori/framework/commit/08c60a878c48198f61b2149127113c359ca5f635))
* Updated Framework Version ([36b0f55](https://github.com/WebFiori/framework/commit/36b0f5514329db80bae9102094e402e240987260))

## [3.0.0-Beta.23](https://github.com/WebFiori/framework/compare/v3.0.1-Beta.22...v3.0.0-Beta.23) (2025-01-07)


### Features

* Added a Method to Load Multiple Files ([89d0363](https://github.com/WebFiori/framework/commit/89d0363bb81a32032e938da71a19ec959c48e2bf))
* Added a Way to Handle Configuration Errors ([76f1539](https://github.com/WebFiori/framework/commit/76f153933680c4ae4d7b067e8fca95273412ab2d))
* Added Ability to Enable or Disable Cache ([434fd72](https://github.com/WebFiori/framework/commit/434fd726657d7e4967681933bd718d60f68f2a76))
* Added Additional Logging Methods to Tasks Manager ([afc9b46](https://github.com/WebFiori/framework/commit/afc9b4697b58dbeb0cb7df26e9303fc1e720ecce))
* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Added Support for Loading Non-PSR-4 Compliant Classes ([a9772b4](https://github.com/WebFiori/framework/commit/a9772b49fc94b1a524e4555a18135461e1ef88ac))
* Added Support for Setting Env Vars Using `putenv()` ([2895d6f](https://github.com/WebFiori/framework/commit/2895d6fd7df6060ebae227867dba719864b3578a))
* Added Support for Writing Unit Test Classes for APIs ([baefa85](https://github.com/WebFiori/framework/commit/baefa855b76a7f42fb2ca0323888d0bc7d7d1f96))
* **autoloader:** Added a Method to Check Validity of Namespace ([e749a3a](https://github.com/WebFiori/framework/commit/e749a3aafa0d6c1a11da7d486cb68ad8b048b4b7))
* Automation of Writing Unit Test Cases for APIs ([5bab349](https://github.com/WebFiori/framework/commit/5bab349082328e668f13c5192dd5555b99201fa9))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Bug Fixes

* Add Missing Returns ([9dcd9bf](https://github.com/WebFiori/framework/commit/9dcd9bf2670116a514169abcfdd5af72d4b12d11))
* Added Check for Empty File Path ([b046fdf](https://github.com/WebFiori/framework/commit/b046fdf98768a63d882d102c1d20cc01b4f8a288))
* Added Handling Code for Session Serialization Errors ([a2c7955](https://github.com/WebFiori/framework/commit/a2c7955888483c4eb8e446c1b5bd8794331a174a))
* Added Missing Namespace ([069364a](https://github.com/WebFiori/framework/commit/069364a4566dc15f917ae0469fb8548ae5411771))
* **autoload:** Add File Name an NS ([eb4d5b9](https://github.com/WebFiori/framework/commit/eb4d5b93f6ea4dc12e5809a6fde63c9f2d4fa928))
* **autoload:** Check NS with Path ([a3d4c6e](https://github.com/WebFiori/framework/commit/a3d4c6e2e52eae4f6c421f533b7316b8562b1bf8))
* Buffer Theme Components as They Might be HTML ([d803352](https://github.com/WebFiori/framework/commit/d803352bb1d97436f242807879e665c24015845a))
* **cli:** Rename of Class `CommandArgument` to `Argument` ([7f67a0f](https://github.com/WebFiori/framework/commit/7f67a0f61886159261c4749955d34a4187e76cbc))
* **config:** Fix to JSON Configuration Style ([4dda36c](https://github.com/WebFiori/framework/commit/4dda36c14c8f8a77479bebb24b7b504e4bf02817))
* Correction to File Path ([df3eacf](https://github.com/WebFiori/framework/commit/df3eacfb150a43020794545f51ee1379256a46fc))
* Fix Assignment Issue ([34a522f](https://github.com/WebFiori/framework/commit/34a522ff53e8ad7bc8bc1287bc0c7595a4d7e254))
* Fix to `RunSQLQueryCommand` ([87dc2e3](https://github.com/WebFiori/framework/commit/87dc2e3a2dbf9f25dc81db2e9af9123aef198d4c))
* Fix to a Bug in Creating Test Case ([0e4b8e5](https://github.com/WebFiori/framework/commit/0e4b8e5ff0c307e45bd5fb3a2acbfacc82f9d373))
* Fix to Bug in Loading Themes ([ce67490](https://github.com/WebFiori/framework/commit/ce674903b6358824c61360a2ae335b8399c38309))
* Fix to Create CLI Command ([82c7a88](https://github.com/WebFiori/framework/commit/82c7a888a0a5140d1381b9855ecf2762eb52659b))
* Fix to Initial Namespace ([6e0e08a](https://github.com/WebFiori/framework/commit/6e0e08ace2b63c2cb959a236342014962c6a3b01))
* Fix to Line Numbers in Exception Logging ([781a233](https://github.com/WebFiori/framework/commit/781a233a9e0bdb95c4d1a40b96358d608e07de0e))
* Fix to Reading Extra Connection Props ([a6c5b92](https://github.com/WebFiori/framework/commit/a6c5b9269ac6f7a354f944f0bbc9557f6a73dd1f))
* Fix to Registering Middleware ([6cc7ce1](https://github.com/WebFiori/framework/commit/6cc7ce1e39bf0aebfe0eb5ff91360e4c4ed04f05))
* Fix to Running SQL Query from File ([0c8bb61](https://github.com/WebFiori/framework/commit/0c8bb613dbdec50c06f80ee0e4d9850602d8a71b))
* Fix to Setting Middleware Name ([3a02a60](https://github.com/WebFiori/framework/commit/3a02a60d0ed3a2decf0059ce889325fc02f64893))
* Fix to Undefined Constant ([d605a5b](https://github.com/WebFiori/framework/commit/d605a5be85cd8623a2114b8c0756372c82bd7c9b))
* Fix to Uninitialized Variable ([905c3c7](https://github.com/WebFiori/framework/commit/905c3c7b8232a8f1ee6171fa309c2489b1bdd141))
* Made `init` Static ([e04233a](https://github.com/WebFiori/framework/commit/e04233a0b4b65b903d92029dcb80ec4814dd5a08))
* Remove Unused Import ([ed43960](https://github.com/WebFiori/framework/commit/ed43960b90052084b7a95a9ac182619af1244a3f))
* **themes:** Fix to Problems in Loading Theme ([7a331ff](https://github.com/WebFiori/framework/commit/7a331ff9484fe13fcbd7a7c653321b3e9d233fba))
* **ui:** Fix to Bug In Web Page Initialization ([8645c2a](https://github.com/WebFiori/framework/commit/8645c2a024276c70a96b0ebc3b83480649bd09d7))
* **ui:** Fix to Load Language After Page Initialization ([38b0843](https://github.com/WebFiori/framework/commit/38b084385251e8b5bfdae2c8ade3ab0219bba046))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Cleanup ([0d5f798](https://github.com/WebFiori/framework/commit/0d5f7983426f7d766e79e6932ec47cf9ac7853dd))
* Code Quality Improvements ([80c7853](https://github.com/WebFiori/framework/commit/80c7853f737b16e605e11fd9bcf56f1ecc24223a))
* Code Quality Improvments ([f8e9ed9](https://github.com/WebFiori/framework/commit/f8e9ed98f1ac4b1c86cceff30a92ffd6107a05d2))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* **dev:** release 3.0.0-Beta.14 ([60aa746](https://github.com/WebFiori/framework/commit/60aa746bf39ccf7cbdda5bd9c24a6ed408d2732c))
* **dev:** release 3.0.0-Beta.14 ([8c3dd76](https://github.com/WebFiori/framework/commit/8c3dd7651f604414c5e5ccfd8567d907545d5513))
* **dev:** release 3.0.0-Beta.17 ([a3e5983](https://github.com/WebFiori/framework/commit/a3e598305d75f2fa87d6148520d88f4235a53253))
* **dev:** release 3.0.0-Beta.18 ([a994097](https://github.com/WebFiori/framework/commit/a994097c021b5575cab7f077f8d730736c3c1bbe))
* **dev:** release 3.0.0-Beta.19 ([e917eb5](https://github.com/WebFiori/framework/commit/e917eb5ccd18ee8223a73ec9a2ac499a281f5764))
* **dev:** release 3.0.0-Beta.19 ([5497825](https://github.com/WebFiori/framework/commit/549782509f23be3a90375debe0319b16e549a3aa))
* **dev:** release 3.0.0-Beta.20 ([7afdb92](https://github.com/WebFiori/framework/commit/7afdb92618bdbc6e11adb29b001d9a9d0a8f4809))
* **dev:** release 3.0.0-Beta.21 ([eef1270](https://github.com/WebFiori/framework/commit/eef1270f51089065f04e7f8843d9944d368a774e))
* **dev:** release 3.0.0-Beta.22 ([0e428f0](https://github.com/WebFiori/framework/commit/0e428f03ef510cb9c33bbf940c92b12ff702c8dd))
* **dev:** release 3.1.0-Beta.14 ([ba5a5e3](https://github.com/WebFiori/framework/commit/ba5a5e30b4033d3f2486cbab578545aadbed67b0))
* Fix Imports ([7386f92](https://github.com/WebFiori/framework/commit/7386f9242351673588eaefe6c0de02c7e467f62a))
* Fix target Branch ([a419a3e](https://github.com/WebFiori/framework/commit/a419a3e29bb416be328b19f1489788d51ebbcd4e))
* Libraries Bump Up ([d79a44a](https://github.com/WebFiori/framework/commit/d79a44a7d2c3e062974031081c3f5dbc24812b56))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* release 3.0.0-Beta.20 ([ddcf6b0](https://github.com/WebFiori/framework/commit/ddcf6b03010a0f011112bfa789f446b1949daaae))
* release 3.0.0-Beta.20 ([c4fc053](https://github.com/WebFiori/framework/commit/c4fc053d45a5c8b24e963a625d0954c33af2c884))
* release 3.0.0-Beta.20 ([6e72830](https://github.com/WebFiori/framework/commit/6e72830f0ec1f6943d84ee3266632d5c62e02832))
* release v3.0.0-Beta.17 ([3c0c639](https://github.com/WebFiori/framework/commit/3c0c639a72f9dd08bec7a150e33af2bb18e9728a))
* release v3.0.0-Beta.18 ([5a588eb](https://github.com/WebFiori/framework/commit/5a588eb52815889a8409a07a30c4e6f0defe3269))
* release v3.0.0-Beta.19 ([e8d5314](https://github.com/WebFiori/framework/commit/e8d531433f6afada6684050013b4169b3d8d547b))
* release v3.0.0-Beta.20 ([630f512](https://github.com/WebFiori/framework/commit/630f512c6a036e111a7b146cacbd7d72dfe0da06))
* release v3.0.0-Beta.21 ([56f0bdc](https://github.com/WebFiori/framework/commit/56f0bdc7917faa97c8d3e4b73076d91213b85366))
* release v3.0.0-Beta.22 ([4271914](https://github.com/WebFiori/framework/commit/4271914182564a0c917a185010933f7f76b86f5d))
* release v3.0.0-Beta.23 ([3f74229](https://github.com/WebFiori/framework/commit/3f74229b2b38449330763f40883a6ce383eda504))
* **release-please:** Added Additional Sections ([40dcfa4](https://github.com/WebFiori/framework/commit/40dcfa4bad0f8b42a34e0541ef558cd78f37b2ce))
* Remove Redeclaration ([f41549d](https://github.com/WebFiori/framework/commit/f41549da7a7570ec9984a53f16abf863a716e55d))
* Remove Unused Import ([4cd7cf3](https://github.com/WebFiori/framework/commit/4cd7cf313f836231c76b4533bacf7f7283589052))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Run CS Fixer ([ca8e690](https://github.com/WebFiori/framework/commit/ca8e690d7e8dcc737d4fe125ea828ec4ef146035))
* Update composer.json ([819c26d](https://github.com/WebFiori/framework/commit/819c26d8fd7f23a057a76fa923b62d0a2281721d))
* Update Libraries Versions ([46f4d56](https://github.com/WebFiori/framework/commit/46f4d56aa8bc911393ed80d4e57368472dbcdd24))
* Updated .gitattribute ([63ba6d8](https://github.com/WebFiori/framework/commit/63ba6d890b82280d87d002f8c3fcfee1493ea2ff))
* Updated .gitattributes ([3b2334c](https://github.com/WebFiori/framework/commit/3b2334ce9b29d1be3b71049b3caf759a00c84724))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated CI Config ([a7175a4](https://github.com/WebFiori/framework/commit/a7175a4442cb6d5d4031d03cf228fc43439b504b))
* Updated Composer Config ([cf26913](https://github.com/WebFiori/framework/commit/cf2691382d6d883f2f06b25e789b9a30524758cd))
* Updated Core Framework Libraries ([9220fa4](https://github.com/WebFiori/framework/commit/9220fa4c77c668793962afc427495adcd6c8ca55))
* Updated Core Libraries ([c21a48f](https://github.com/WebFiori/framework/commit/c21a48f6586068b4cab3c223465e4b3c60849752))
* Updated Core Libraries ([fda39a9](https://github.com/WebFiori/framework/commit/fda39a9168b6e8cebda1408e0de4f0f3815845f5))
* Updated Core Libraries ([4aa9670](https://github.com/WebFiori/framework/commit/4aa96707feabd9518a788d4393442b0287f0a375))
* Updated Core Libraries Versions ([dcb1a15](https://github.com/WebFiori/framework/commit/dcb1a15882cac069cb7439101b8e096018c1cfc1))
* Updated Core Library Version ([db4d223](https://github.com/WebFiori/framework/commit/db4d223a4cb2b8bc40a45fa69e4d67b358d1f29a))
* Updated Dependences ([a160f0f](https://github.com/WebFiori/framework/commit/a160f0fcc7cb0b2570c9487090b2bcc3e0ad658e))
* Updated Dependencies ([e48f333](https://github.com/WebFiori/framework/commit/e48f3336d8c0b1910e18b8baa5ea40be28c9e50d))
* Updated Dependencies ([8284dc6](https://github.com/WebFiori/framework/commit/8284dc655e8e92aafc3fb6a1bd88861254da0fe1))
* Updated Dependencies ([97bb7a2](https://github.com/WebFiori/framework/commit/97bb7a220a9c8a81fd70a4c2d80d891e4f4c7eb2))
* Updated Dependencies ([aef4319](https://github.com/WebFiori/framework/commit/aef4319b1dd10cd4f208e943e638b4b364b04cd0))
* Updated Dependencies + Framework Version ([9f5dd93](https://github.com/WebFiori/framework/commit/9f5dd9374ebf818605b6ae4acff3d5b95237d1ff))
* Updated Dependencies Version ([0d3ead5](https://github.com/WebFiori/framework/commit/0d3ead5cad177efd50e4b285222fcdbaf8beab66))
* Updated Dependencies Versions ([07252d0](https://github.com/WebFiori/framework/commit/07252d09f40af27cc04494ea7081a41fe0fe2ede))
* Updated Errors Handling Library ([5cf44a9](https://github.com/WebFiori/framework/commit/5cf44a9b5ecae3ac5ed3888c18c33e5415055703))
* Updated Framework Version ([834782c](https://github.com/WebFiori/framework/commit/834782c8fd1ae846ffbaa72b8ee76e5fc7796f56))
* Updated Framework Version ([7f84cf6](https://github.com/WebFiori/framework/commit/7f84cf65da991a63daccc2cb0896b78b98d578c5))
* Updated Framework Version ([361e5d5](https://github.com/WebFiori/framework/commit/361e5d545a274043efceab4087d4db7769990d60))
* Updated Framework Version ([783f4be](https://github.com/WebFiori/framework/commit/783f4be57869ae93eab8c0b49fe2ede5cc7fbba8))
* Updated Framework Version ([0403027](https://github.com/WebFiori/framework/commit/0403027fc02bfbba13dd1c899ae073f43c925cd8))
* Updated Framework Version ([f7c0f7f](https://github.com/WebFiori/framework/commit/f7c0f7f0dad2900d988b3866c2a035b0b1c10e7b))
* Updated Framework Version ([d44cedc](https://github.com/WebFiori/framework/commit/d44cedc29ce9097403bdb263c279812f78d7581b))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
* Updated Framework Version ([fb91c15](https://github.com/WebFiori/framework/commit/fb91c15587fb006f096b90a2f2b01e5b9ffb7c47))
* Updated Framework Version ([a817e8f](https://github.com/WebFiori/framework/commit/a817e8feec235cec29e6c20e00732a25d1c80534))
* Updated Framework Version ([1672faf](https://github.com/WebFiori/framework/commit/1672faf3f12ef217915d5ea62d6fae9e01c9db28))
* Updated Framework Version ([8b013ae](https://github.com/WebFiori/framework/commit/8b013ae6d622823f4abd935cbda45d0a718030a1))
* Updated Framework Version ([7841fd2](https://github.com/WebFiori/framework/commit/7841fd266b92146c4eb800adc1b859c9e022dd25))
* Updated Framework Version Number ([2f8d814](https://github.com/WebFiori/framework/commit/2f8d814fd477b3c8699ada67c2c6b47a04f29b10))
* Updated Framework Version Number ([84c7857](https://github.com/WebFiori/framework/commit/84c785722512e04c106412f3612f9ef59758ed40))
* Updated Framework Version Number ([72cb62d](https://github.com/WebFiori/framework/commit/72cb62d198cef51275c282142edacb13b1a5bcd4))
* Updated Framework Version Number ([bc89447](https://github.com/WebFiori/framework/commit/bc8944771dd1bd629f65a0df9e4067ea76e141da))
* Updated Framework Version Number ([a5177bb](https://github.com/WebFiori/framework/commit/a5177bbb1de08d1cf6748ec09ec6d9e53fa20d10))
* Updated Libraries Versions ([248d1da](https://github.com/WebFiori/framework/commit/248d1dab9efffd44ac5728d005dde58470d2fef1))
* Updated Release Please Config ([1a8b4e5](https://github.com/WebFiori/framework/commit/1a8b4e55f9a5496aac47e30228613d8a24068914))
* Updated Version Number ([280f418](https://github.com/WebFiori/framework/commit/280f418df38a2eb019b6f2a5d0c1b8b8d00133d3))
* Updated Version Number ([d75c9d0](https://github.com/WebFiori/framework/commit/d75c9d0c9547d2e4ce3edbac839a1f712a9f90a4))

## [3.0.0-Beta.22](https://github.com/WebFiori/framework/compare/v3.0.1-Beta.21...v3.0.0-Beta.22) (2024-12-24)


### Features

* Added a Method to Load Multiple Files ([89d0363](https://github.com/WebFiori/framework/commit/89d0363bb81a32032e938da71a19ec959c48e2bf))
* Added a Way to Handle Configuration Errors ([76f1539](https://github.com/WebFiori/framework/commit/76f153933680c4ae4d7b067e8fca95273412ab2d))
* Added Ability to Enable or Disable Cache ([434fd72](https://github.com/WebFiori/framework/commit/434fd726657d7e4967681933bd718d60f68f2a76))
* Added Additional Logging Methods to Tasks Manager ([afc9b46](https://github.com/WebFiori/framework/commit/afc9b4697b58dbeb0cb7df26e9303fc1e720ecce))
* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Added Support for Loading Non-PSR-4 Compliant Classes ([a9772b4](https://github.com/WebFiori/framework/commit/a9772b49fc94b1a524e4555a18135461e1ef88ac))
* Added Support for Setting Env Vars Using `putenv()` ([2895d6f](https://github.com/WebFiori/framework/commit/2895d6fd7df6060ebae227867dba719864b3578a))
* Added Support for Writing Unit Test Classes for APIs ([baefa85](https://github.com/WebFiori/framework/commit/baefa855b76a7f42fb2ca0323888d0bc7d7d1f96))
* **autoloader:** Added a Method to Check Validity of Namespace ([e749a3a](https://github.com/WebFiori/framework/commit/e749a3aafa0d6c1a11da7d486cb68ad8b048b4b7))
* Automation of Writing Unit Test Cases for APIs ([5bab349](https://github.com/WebFiori/framework/commit/5bab349082328e668f13c5192dd5555b99201fa9))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Bug Fixes

* Add Missing Returns ([9dcd9bf](https://github.com/WebFiori/framework/commit/9dcd9bf2670116a514169abcfdd5af72d4b12d11))
* Added Check for Empty File Path ([b046fdf](https://github.com/WebFiori/framework/commit/b046fdf98768a63d882d102c1d20cc01b4f8a288))
* Added Handling Code for Session Serialization Errors ([a2c7955](https://github.com/WebFiori/framework/commit/a2c7955888483c4eb8e446c1b5bd8794331a174a))
* Added Missing Namespace ([069364a](https://github.com/WebFiori/framework/commit/069364a4566dc15f917ae0469fb8548ae5411771))
* **autoload:** Add File Name an NS ([eb4d5b9](https://github.com/WebFiori/framework/commit/eb4d5b93f6ea4dc12e5809a6fde63c9f2d4fa928))
* **autoload:** Check NS with Path ([a3d4c6e](https://github.com/WebFiori/framework/commit/a3d4c6e2e52eae4f6c421f533b7316b8562b1bf8))
* **cli:** Rename of Class `CommandArgument` to `Argument` ([7f67a0f](https://github.com/WebFiori/framework/commit/7f67a0f61886159261c4749955d34a4187e76cbc))
* **config:** Fix to JSON Configuration Style ([4dda36c](https://github.com/WebFiori/framework/commit/4dda36c14c8f8a77479bebb24b7b504e4bf02817))
* Correction to File Path ([df3eacf](https://github.com/WebFiori/framework/commit/df3eacfb150a43020794545f51ee1379256a46fc))
* Fix Assignment Issue ([34a522f](https://github.com/WebFiori/framework/commit/34a522ff53e8ad7bc8bc1287bc0c7595a4d7e254))
* Fix to `RunSQLQueryCommand` ([87dc2e3](https://github.com/WebFiori/framework/commit/87dc2e3a2dbf9f25dc81db2e9af9123aef198d4c))
* Fix to a Bug in Creating Test Case ([0e4b8e5](https://github.com/WebFiori/framework/commit/0e4b8e5ff0c307e45bd5fb3a2acbfacc82f9d373))
* Fix to Bug in Loading Themes ([ce67490](https://github.com/WebFiori/framework/commit/ce674903b6358824c61360a2ae335b8399c38309))
* Fix to Create CLI Command ([82c7a88](https://github.com/WebFiori/framework/commit/82c7a888a0a5140d1381b9855ecf2762eb52659b))
* Fix to Initial Namespace ([6e0e08a](https://github.com/WebFiori/framework/commit/6e0e08ace2b63c2cb959a236342014962c6a3b01))
* Fix to Line Numbers in Exception Logging ([781a233](https://github.com/WebFiori/framework/commit/781a233a9e0bdb95c4d1a40b96358d608e07de0e))
* Fix to Reading Extra Connection Props ([a6c5b92](https://github.com/WebFiori/framework/commit/a6c5b9269ac6f7a354f944f0bbc9557f6a73dd1f))
* Fix to Registering Middleware ([6cc7ce1](https://github.com/WebFiori/framework/commit/6cc7ce1e39bf0aebfe0eb5ff91360e4c4ed04f05))
* Fix to Running SQL Query from File ([0c8bb61](https://github.com/WebFiori/framework/commit/0c8bb613dbdec50c06f80ee0e4d9850602d8a71b))
* Fix to Setting Middleware Name ([3a02a60](https://github.com/WebFiori/framework/commit/3a02a60d0ed3a2decf0059ce889325fc02f64893))
* Fix to Undefined Constant ([d605a5b](https://github.com/WebFiori/framework/commit/d605a5be85cd8623a2114b8c0756372c82bd7c9b))
* Fix to Uninitialized Variable ([905c3c7](https://github.com/WebFiori/framework/commit/905c3c7b8232a8f1ee6171fa309c2489b1bdd141))
* Made `init` Static ([e04233a](https://github.com/WebFiori/framework/commit/e04233a0b4b65b903d92029dcb80ec4814dd5a08))
* Remove Unused Import ([ed43960](https://github.com/WebFiori/framework/commit/ed43960b90052084b7a95a9ac182619af1244a3f))
* **themes:** Fix to Problems in Loading Theme ([7a331ff](https://github.com/WebFiori/framework/commit/7a331ff9484fe13fcbd7a7c653321b3e9d233fba))
* **ui:** Fix to Bug In Web Page Initialization ([8645c2a](https://github.com/WebFiori/framework/commit/8645c2a024276c70a96b0ebc3b83480649bd09d7))
* **ui:** Fix to Load Language After Page Initialization ([38b0843](https://github.com/WebFiori/framework/commit/38b084385251e8b5bfdae2c8ade3ab0219bba046))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Cleanup ([0d5f798](https://github.com/WebFiori/framework/commit/0d5f7983426f7d766e79e6932ec47cf9ac7853dd))
* Code Quality Improvements ([80c7853](https://github.com/WebFiori/framework/commit/80c7853f737b16e605e11fd9bcf56f1ecc24223a))
* Code Quality Improvments ([f8e9ed9](https://github.com/WebFiori/framework/commit/f8e9ed98f1ac4b1c86cceff30a92ffd6107a05d2))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* **dev:** release 3.0.0-Beta.14 ([60aa746](https://github.com/WebFiori/framework/commit/60aa746bf39ccf7cbdda5bd9c24a6ed408d2732c))
* **dev:** release 3.0.0-Beta.14 ([8c3dd76](https://github.com/WebFiori/framework/commit/8c3dd7651f604414c5e5ccfd8567d907545d5513))
* **dev:** release 3.0.0-Beta.17 ([a3e5983](https://github.com/WebFiori/framework/commit/a3e598305d75f2fa87d6148520d88f4235a53253))
* **dev:** release 3.0.0-Beta.18 ([a994097](https://github.com/WebFiori/framework/commit/a994097c021b5575cab7f077f8d730736c3c1bbe))
* **dev:** release 3.0.0-Beta.19 ([e917eb5](https://github.com/WebFiori/framework/commit/e917eb5ccd18ee8223a73ec9a2ac499a281f5764))
* **dev:** release 3.0.0-Beta.19 ([5497825](https://github.com/WebFiori/framework/commit/549782509f23be3a90375debe0319b16e549a3aa))
* **dev:** release 3.0.0-Beta.20 ([7afdb92](https://github.com/WebFiori/framework/commit/7afdb92618bdbc6e11adb29b001d9a9d0a8f4809))
* **dev:** release 3.0.0-Beta.21 ([eef1270](https://github.com/WebFiori/framework/commit/eef1270f51089065f04e7f8843d9944d368a774e))
* **dev:** release 3.1.0-Beta.14 ([ba5a5e3](https://github.com/WebFiori/framework/commit/ba5a5e30b4033d3f2486cbab578545aadbed67b0))
* Fix Imports ([7386f92](https://github.com/WebFiori/framework/commit/7386f9242351673588eaefe6c0de02c7e467f62a))
* Fix target Branch ([a419a3e](https://github.com/WebFiori/framework/commit/a419a3e29bb416be328b19f1489788d51ebbcd4e))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* release 3.0.0-Beta.20 ([ddcf6b0](https://github.com/WebFiori/framework/commit/ddcf6b03010a0f011112bfa789f446b1949daaae))
* release 3.0.0-Beta.20 ([c4fc053](https://github.com/WebFiori/framework/commit/c4fc053d45a5c8b24e963a625d0954c33af2c884))
* release 3.0.0-Beta.20 ([6e72830](https://github.com/WebFiori/framework/commit/6e72830f0ec1f6943d84ee3266632d5c62e02832))
* release v3.0.0-Beta.17 ([3c0c639](https://github.com/WebFiori/framework/commit/3c0c639a72f9dd08bec7a150e33af2bb18e9728a))
* release v3.0.0-Beta.18 ([5a588eb](https://github.com/WebFiori/framework/commit/5a588eb52815889a8409a07a30c4e6f0defe3269))
* release v3.0.0-Beta.19 ([e8d5314](https://github.com/WebFiori/framework/commit/e8d531433f6afada6684050013b4169b3d8d547b))
* release v3.0.0-Beta.20 ([630f512](https://github.com/WebFiori/framework/commit/630f512c6a036e111a7b146cacbd7d72dfe0da06))
* release v3.0.0-Beta.21 ([56f0bdc](https://github.com/WebFiori/framework/commit/56f0bdc7917faa97c8d3e4b73076d91213b85366))
* release v3.0.0-Beta.22 ([4271914](https://github.com/WebFiori/framework/commit/4271914182564a0c917a185010933f7f76b86f5d))
* **release-please:** Added Additional Sections ([40dcfa4](https://github.com/WebFiori/framework/commit/40dcfa4bad0f8b42a34e0541ef558cd78f37b2ce))
* Remove Redeclaration ([f41549d](https://github.com/WebFiori/framework/commit/f41549da7a7570ec9984a53f16abf863a716e55d))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Run CS Fixer ([ca8e690](https://github.com/WebFiori/framework/commit/ca8e690d7e8dcc737d4fe125ea828ec4ef146035))
* Update composer.json ([819c26d](https://github.com/WebFiori/framework/commit/819c26d8fd7f23a057a76fa923b62d0a2281721d))
* Update Libraries Versions ([46f4d56](https://github.com/WebFiori/framework/commit/46f4d56aa8bc911393ed80d4e57368472dbcdd24))
* Updated .gitattribute ([63ba6d8](https://github.com/WebFiori/framework/commit/63ba6d890b82280d87d002f8c3fcfee1493ea2ff))
* Updated .gitattributes ([3b2334c](https://github.com/WebFiori/framework/commit/3b2334ce9b29d1be3b71049b3caf759a00c84724))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated CI Config ([a7175a4](https://github.com/WebFiori/framework/commit/a7175a4442cb6d5d4031d03cf228fc43439b504b))
* Updated Composer Config ([cf26913](https://github.com/WebFiori/framework/commit/cf2691382d6d883f2f06b25e789b9a30524758cd))
* Updated Core Framework Libraries ([9220fa4](https://github.com/WebFiori/framework/commit/9220fa4c77c668793962afc427495adcd6c8ca55))
* Updated Core Libraries ([c21a48f](https://github.com/WebFiori/framework/commit/c21a48f6586068b4cab3c223465e4b3c60849752))
* Updated Core Libraries ([fda39a9](https://github.com/WebFiori/framework/commit/fda39a9168b6e8cebda1408e0de4f0f3815845f5))
* Updated Core Libraries ([4aa9670](https://github.com/WebFiori/framework/commit/4aa96707feabd9518a788d4393442b0287f0a375))
* Updated Core Libraries Versions ([dcb1a15](https://github.com/WebFiori/framework/commit/dcb1a15882cac069cb7439101b8e096018c1cfc1))
* Updated Core Library Version ([db4d223](https://github.com/WebFiori/framework/commit/db4d223a4cb2b8bc40a45fa69e4d67b358d1f29a))
* Updated Dependences ([a160f0f](https://github.com/WebFiori/framework/commit/a160f0fcc7cb0b2570c9487090b2bcc3e0ad658e))
* Updated Dependencies ([e48f333](https://github.com/WebFiori/framework/commit/e48f3336d8c0b1910e18b8baa5ea40be28c9e50d))
* Updated Dependencies ([8284dc6](https://github.com/WebFiori/framework/commit/8284dc655e8e92aafc3fb6a1bd88861254da0fe1))
* Updated Dependencies ([97bb7a2](https://github.com/WebFiori/framework/commit/97bb7a220a9c8a81fd70a4c2d80d891e4f4c7eb2))
* Updated Dependencies ([aef4319](https://github.com/WebFiori/framework/commit/aef4319b1dd10cd4f208e943e638b4b364b04cd0))
* Updated Dependencies + Framework Version ([9f5dd93](https://github.com/WebFiori/framework/commit/9f5dd9374ebf818605b6ae4acff3d5b95237d1ff))
* Updated Dependencies Version ([0d3ead5](https://github.com/WebFiori/framework/commit/0d3ead5cad177efd50e4b285222fcdbaf8beab66))
* Updated Dependencies Versions ([07252d0](https://github.com/WebFiori/framework/commit/07252d09f40af27cc04494ea7081a41fe0fe2ede))
* Updated Errors Handling Library ([5cf44a9](https://github.com/WebFiori/framework/commit/5cf44a9b5ecae3ac5ed3888c18c33e5415055703))
* Updated Framework Version ([7f84cf6](https://github.com/WebFiori/framework/commit/7f84cf65da991a63daccc2cb0896b78b98d578c5))
* Updated Framework Version ([361e5d5](https://github.com/WebFiori/framework/commit/361e5d545a274043efceab4087d4db7769990d60))
* Updated Framework Version ([783f4be](https://github.com/WebFiori/framework/commit/783f4be57869ae93eab8c0b49fe2ede5cc7fbba8))
* Updated Framework Version ([0403027](https://github.com/WebFiori/framework/commit/0403027fc02bfbba13dd1c899ae073f43c925cd8))
* Updated Framework Version ([f7c0f7f](https://github.com/WebFiori/framework/commit/f7c0f7f0dad2900d988b3866c2a035b0b1c10e7b))
* Updated Framework Version ([d44cedc](https://github.com/WebFiori/framework/commit/d44cedc29ce9097403bdb263c279812f78d7581b))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
* Updated Framework Version ([fb91c15](https://github.com/WebFiori/framework/commit/fb91c15587fb006f096b90a2f2b01e5b9ffb7c47))
* Updated Framework Version ([a817e8f](https://github.com/WebFiori/framework/commit/a817e8feec235cec29e6c20e00732a25d1c80534))
* Updated Framework Version ([1672faf](https://github.com/WebFiori/framework/commit/1672faf3f12ef217915d5ea62d6fae9e01c9db28))
* Updated Framework Version ([8b013ae](https://github.com/WebFiori/framework/commit/8b013ae6d622823f4abd935cbda45d0a718030a1))
* Updated Framework Version ([7841fd2](https://github.com/WebFiori/framework/commit/7841fd266b92146c4eb800adc1b859c9e022dd25))
* Updated Framework Version Number ([2f8d814](https://github.com/WebFiori/framework/commit/2f8d814fd477b3c8699ada67c2c6b47a04f29b10))
* Updated Framework Version Number ([84c7857](https://github.com/WebFiori/framework/commit/84c785722512e04c106412f3612f9ef59758ed40))
* Updated Framework Version Number ([72cb62d](https://github.com/WebFiori/framework/commit/72cb62d198cef51275c282142edacb13b1a5bcd4))
* Updated Framework Version Number ([bc89447](https://github.com/WebFiori/framework/commit/bc8944771dd1bd629f65a0df9e4067ea76e141da))
* Updated Framework Version Number ([a5177bb](https://github.com/WebFiori/framework/commit/a5177bbb1de08d1cf6748ec09ec6d9e53fa20d10))
* Updated Libraries Versions ([248d1da](https://github.com/WebFiori/framework/commit/248d1dab9efffd44ac5728d005dde58470d2fef1))
* Updated Release Please Config ([1a8b4e5](https://github.com/WebFiori/framework/commit/1a8b4e55f9a5496aac47e30228613d8a24068914))
* Updated Version Number ([280f418](https://github.com/WebFiori/framework/commit/280f418df38a2eb019b6f2a5d0c1b8b8d00133d3))
* Updated Version Number ([d75c9d0](https://github.com/WebFiori/framework/commit/d75c9d0c9547d2e4ce3edbac839a1f712a9f90a4))

## [3.0.0-Beta.21](https://github.com/WebFiori/framework/compare/v3.0.1-Beta.20...v3.0.0-Beta.21) (2024-12-24)


### Features

* Added a Method to Load Multiple Files ([89d0363](https://github.com/WebFiori/framework/commit/89d0363bb81a32032e938da71a19ec959c48e2bf))
* Added a Way to Handle Configuration Errors ([76f1539](https://github.com/WebFiori/framework/commit/76f153933680c4ae4d7b067e8fca95273412ab2d))
* Added Ability to Enable or Disable Cache ([434fd72](https://github.com/WebFiori/framework/commit/434fd726657d7e4967681933bd718d60f68f2a76))
* Added Additional Logging Methods to Tasks Manager ([afc9b46](https://github.com/WebFiori/framework/commit/afc9b4697b58dbeb0cb7df26e9303fc1e720ecce))
* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Added Support for Loading Non-PSR-4 Compliant Classes ([a9772b4](https://github.com/WebFiori/framework/commit/a9772b49fc94b1a524e4555a18135461e1ef88ac))
* Added Support for Setting Env Vars Using `putenv()` ([2895d6f](https://github.com/WebFiori/framework/commit/2895d6fd7df6060ebae227867dba719864b3578a))
* Added Support for Writing Unit Test Classes for APIs ([baefa85](https://github.com/WebFiori/framework/commit/baefa855b76a7f42fb2ca0323888d0bc7d7d1f96))
* **autoloader:** Added a Method to Check Validity of Namespace ([e749a3a](https://github.com/WebFiori/framework/commit/e749a3aafa0d6c1a11da7d486cb68ad8b048b4b7))
* Automation of Writing Unit Test Cases for APIs ([5bab349](https://github.com/WebFiori/framework/commit/5bab349082328e668f13c5192dd5555b99201fa9))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Bug Fixes

* Add Missing Returns ([9dcd9bf](https://github.com/WebFiori/framework/commit/9dcd9bf2670116a514169abcfdd5af72d4b12d11))
* Added Check for Empty File Path ([b046fdf](https://github.com/WebFiori/framework/commit/b046fdf98768a63d882d102c1d20cc01b4f8a288))
* Added Handling Code for Session Serialization Errors ([a2c7955](https://github.com/WebFiori/framework/commit/a2c7955888483c4eb8e446c1b5bd8794331a174a))
* Added Missing Namespace ([069364a](https://github.com/WebFiori/framework/commit/069364a4566dc15f917ae0469fb8548ae5411771))
* **autoload:** Add File Name an NS ([eb4d5b9](https://github.com/WebFiori/framework/commit/eb4d5b93f6ea4dc12e5809a6fde63c9f2d4fa928))
* **autoload:** Check NS with Path ([a3d4c6e](https://github.com/WebFiori/framework/commit/a3d4c6e2e52eae4f6c421f533b7316b8562b1bf8))
* **cli:** Rename of Class `CommandArgument` to `Argument` ([7f67a0f](https://github.com/WebFiori/framework/commit/7f67a0f61886159261c4749955d34a4187e76cbc))
* **config:** Fix to JSON Configuration Style ([4dda36c](https://github.com/WebFiori/framework/commit/4dda36c14c8f8a77479bebb24b7b504e4bf02817))
* Correction to File Path ([df3eacf](https://github.com/WebFiori/framework/commit/df3eacfb150a43020794545f51ee1379256a46fc))
* Fix Assignment Issue ([34a522f](https://github.com/WebFiori/framework/commit/34a522ff53e8ad7bc8bc1287bc0c7595a4d7e254))
* Fix to `RunSQLQueryCommand` ([87dc2e3](https://github.com/WebFiori/framework/commit/87dc2e3a2dbf9f25dc81db2e9af9123aef198d4c))
* Fix to a Bug in Creating Test Case ([0e4b8e5](https://github.com/WebFiori/framework/commit/0e4b8e5ff0c307e45bd5fb3a2acbfacc82f9d373))
* Fix to Bug in Loading Themes ([ce67490](https://github.com/WebFiori/framework/commit/ce674903b6358824c61360a2ae335b8399c38309))
* Fix to Create CLI Command ([82c7a88](https://github.com/WebFiori/framework/commit/82c7a888a0a5140d1381b9855ecf2762eb52659b))
* Fix to Initial Namespace ([6e0e08a](https://github.com/WebFiori/framework/commit/6e0e08ace2b63c2cb959a236342014962c6a3b01))
* Fix to Line Numbers in Exception Logging ([781a233](https://github.com/WebFiori/framework/commit/781a233a9e0bdb95c4d1a40b96358d608e07de0e))
* Fix to Reading Extra Connection Props ([a6c5b92](https://github.com/WebFiori/framework/commit/a6c5b9269ac6f7a354f944f0bbc9557f6a73dd1f))
* Fix to Registering Middleware ([6cc7ce1](https://github.com/WebFiori/framework/commit/6cc7ce1e39bf0aebfe0eb5ff91360e4c4ed04f05))
* Fix to Running SQL Query from File ([0c8bb61](https://github.com/WebFiori/framework/commit/0c8bb613dbdec50c06f80ee0e4d9850602d8a71b))
* Fix to Setting Middleware Name ([3a02a60](https://github.com/WebFiori/framework/commit/3a02a60d0ed3a2decf0059ce889325fc02f64893))
* Fix to Undefined Constant ([d605a5b](https://github.com/WebFiori/framework/commit/d605a5be85cd8623a2114b8c0756372c82bd7c9b))
* Fix to Uninitialized Variable ([905c3c7](https://github.com/WebFiori/framework/commit/905c3c7b8232a8f1ee6171fa309c2489b1bdd141))
* Made `init` Static ([e04233a](https://github.com/WebFiori/framework/commit/e04233a0b4b65b903d92029dcb80ec4814dd5a08))
* Remove Unused Import ([ed43960](https://github.com/WebFiori/framework/commit/ed43960b90052084b7a95a9ac182619af1244a3f))
* **themes:** Fix to Problems in Loading Theme ([7a331ff](https://github.com/WebFiori/framework/commit/7a331ff9484fe13fcbd7a7c653321b3e9d233fba))
* **ui:** Fix to Bug In Web Page Initialization ([8645c2a](https://github.com/WebFiori/framework/commit/8645c2a024276c70a96b0ebc3b83480649bd09d7))
* **ui:** Fix to Load Language After Page Initialization ([38b0843](https://github.com/WebFiori/framework/commit/38b084385251e8b5bfdae2c8ade3ab0219bba046))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Cleanup ([0d5f798](https://github.com/WebFiori/framework/commit/0d5f7983426f7d766e79e6932ec47cf9ac7853dd))
* Code Quality Improvements ([80c7853](https://github.com/WebFiori/framework/commit/80c7853f737b16e605e11fd9bcf56f1ecc24223a))
* Code Quality Improvments ([f8e9ed9](https://github.com/WebFiori/framework/commit/f8e9ed98f1ac4b1c86cceff30a92ffd6107a05d2))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* **dev:** release 3.0.0-Beta.14 ([60aa746](https://github.com/WebFiori/framework/commit/60aa746bf39ccf7cbdda5bd9c24a6ed408d2732c))
* **dev:** release 3.0.0-Beta.14 ([8c3dd76](https://github.com/WebFiori/framework/commit/8c3dd7651f604414c5e5ccfd8567d907545d5513))
* **dev:** release 3.0.0-Beta.17 ([a3e5983](https://github.com/WebFiori/framework/commit/a3e598305d75f2fa87d6148520d88f4235a53253))
* **dev:** release 3.0.0-Beta.18 ([a994097](https://github.com/WebFiori/framework/commit/a994097c021b5575cab7f077f8d730736c3c1bbe))
* **dev:** release 3.0.0-Beta.19 ([e917eb5](https://github.com/WebFiori/framework/commit/e917eb5ccd18ee8223a73ec9a2ac499a281f5764))
* **dev:** release 3.0.0-Beta.19 ([5497825](https://github.com/WebFiori/framework/commit/549782509f23be3a90375debe0319b16e549a3aa))
* **dev:** release 3.0.0-Beta.20 ([7afdb92](https://github.com/WebFiori/framework/commit/7afdb92618bdbc6e11adb29b001d9a9d0a8f4809))
* **dev:** release 3.1.0-Beta.14 ([ba5a5e3](https://github.com/WebFiori/framework/commit/ba5a5e30b4033d3f2486cbab578545aadbed67b0))
* Fix Imports ([7386f92](https://github.com/WebFiori/framework/commit/7386f9242351673588eaefe6c0de02c7e467f62a))
* Fix target Branch ([a419a3e](https://github.com/WebFiori/framework/commit/a419a3e29bb416be328b19f1489788d51ebbcd4e))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* release 3.0.0-Beta.20 ([ddcf6b0](https://github.com/WebFiori/framework/commit/ddcf6b03010a0f011112bfa789f446b1949daaae))
* release 3.0.0-Beta.20 ([c4fc053](https://github.com/WebFiori/framework/commit/c4fc053d45a5c8b24e963a625d0954c33af2c884))
* release 3.0.0-Beta.20 ([6e72830](https://github.com/WebFiori/framework/commit/6e72830f0ec1f6943d84ee3266632d5c62e02832))
* release v3.0.0-Beta.17 ([3c0c639](https://github.com/WebFiori/framework/commit/3c0c639a72f9dd08bec7a150e33af2bb18e9728a))
* release v3.0.0-Beta.18 ([5a588eb](https://github.com/WebFiori/framework/commit/5a588eb52815889a8409a07a30c4e6f0defe3269))
* release v3.0.0-Beta.19 ([e8d5314](https://github.com/WebFiori/framework/commit/e8d531433f6afada6684050013b4169b3d8d547b))
* release v3.0.0-Beta.20 ([630f512](https://github.com/WebFiori/framework/commit/630f512c6a036e111a7b146cacbd7d72dfe0da06))
* release v3.0.0-Beta.21 ([56f0bdc](https://github.com/WebFiori/framework/commit/56f0bdc7917faa97c8d3e4b73076d91213b85366))
* **release-please:** Added Additional Sections ([40dcfa4](https://github.com/WebFiori/framework/commit/40dcfa4bad0f8b42a34e0541ef558cd78f37b2ce))
* Remove Redeclaration ([f41549d](https://github.com/WebFiori/framework/commit/f41549da7a7570ec9984a53f16abf863a716e55d))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Run CS Fixer ([ca8e690](https://github.com/WebFiori/framework/commit/ca8e690d7e8dcc737d4fe125ea828ec4ef146035))
* Update composer.json ([819c26d](https://github.com/WebFiori/framework/commit/819c26d8fd7f23a057a76fa923b62d0a2281721d))
* Update Libraries Versions ([46f4d56](https://github.com/WebFiori/framework/commit/46f4d56aa8bc911393ed80d4e57368472dbcdd24))
* Updated .gitattribute ([63ba6d8](https://github.com/WebFiori/framework/commit/63ba6d890b82280d87d002f8c3fcfee1493ea2ff))
* Updated .gitattributes ([3b2334c](https://github.com/WebFiori/framework/commit/3b2334ce9b29d1be3b71049b3caf759a00c84724))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated CI Config ([a7175a4](https://github.com/WebFiori/framework/commit/a7175a4442cb6d5d4031d03cf228fc43439b504b))
* Updated Composer Config ([cf26913](https://github.com/WebFiori/framework/commit/cf2691382d6d883f2f06b25e789b9a30524758cd))
* Updated Core Framework Libraries ([9220fa4](https://github.com/WebFiori/framework/commit/9220fa4c77c668793962afc427495adcd6c8ca55))
* Updated Core Libraries ([c21a48f](https://github.com/WebFiori/framework/commit/c21a48f6586068b4cab3c223465e4b3c60849752))
* Updated Core Libraries ([fda39a9](https://github.com/WebFiori/framework/commit/fda39a9168b6e8cebda1408e0de4f0f3815845f5))
* Updated Core Libraries ([4aa9670](https://github.com/WebFiori/framework/commit/4aa96707feabd9518a788d4393442b0287f0a375))
* Updated Core Libraries Versions ([dcb1a15](https://github.com/WebFiori/framework/commit/dcb1a15882cac069cb7439101b8e096018c1cfc1))
* Updated Core Library Version ([db4d223](https://github.com/WebFiori/framework/commit/db4d223a4cb2b8bc40a45fa69e4d67b358d1f29a))
* Updated Dependences ([a160f0f](https://github.com/WebFiori/framework/commit/a160f0fcc7cb0b2570c9487090b2bcc3e0ad658e))
* Updated Dependencies ([e48f333](https://github.com/WebFiori/framework/commit/e48f3336d8c0b1910e18b8baa5ea40be28c9e50d))
* Updated Dependencies ([8284dc6](https://github.com/WebFiori/framework/commit/8284dc655e8e92aafc3fb6a1bd88861254da0fe1))
* Updated Dependencies ([97bb7a2](https://github.com/WebFiori/framework/commit/97bb7a220a9c8a81fd70a4c2d80d891e4f4c7eb2))
* Updated Dependencies ([aef4319](https://github.com/WebFiori/framework/commit/aef4319b1dd10cd4f208e943e638b4b364b04cd0))
* Updated Dependencies Version ([0d3ead5](https://github.com/WebFiori/framework/commit/0d3ead5cad177efd50e4b285222fcdbaf8beab66))
* Updated Dependencies Versions ([07252d0](https://github.com/WebFiori/framework/commit/07252d09f40af27cc04494ea7081a41fe0fe2ede))
* Updated Errors Handling Library ([5cf44a9](https://github.com/WebFiori/framework/commit/5cf44a9b5ecae3ac5ed3888c18c33e5415055703))
* Updated Framework Version ([7f84cf6](https://github.com/WebFiori/framework/commit/7f84cf65da991a63daccc2cb0896b78b98d578c5))
* Updated Framework Version ([361e5d5](https://github.com/WebFiori/framework/commit/361e5d545a274043efceab4087d4db7769990d60))
* Updated Framework Version ([783f4be](https://github.com/WebFiori/framework/commit/783f4be57869ae93eab8c0b49fe2ede5cc7fbba8))
* Updated Framework Version ([0403027](https://github.com/WebFiori/framework/commit/0403027fc02bfbba13dd1c899ae073f43c925cd8))
* Updated Framework Version ([f7c0f7f](https://github.com/WebFiori/framework/commit/f7c0f7f0dad2900d988b3866c2a035b0b1c10e7b))
* Updated Framework Version ([d44cedc](https://github.com/WebFiori/framework/commit/d44cedc29ce9097403bdb263c279812f78d7581b))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
* Updated Framework Version ([fb91c15](https://github.com/WebFiori/framework/commit/fb91c15587fb006f096b90a2f2b01e5b9ffb7c47))
* Updated Framework Version ([a817e8f](https://github.com/WebFiori/framework/commit/a817e8feec235cec29e6c20e00732a25d1c80534))
* Updated Framework Version ([1672faf](https://github.com/WebFiori/framework/commit/1672faf3f12ef217915d5ea62d6fae9e01c9db28))
* Updated Framework Version ([8b013ae](https://github.com/WebFiori/framework/commit/8b013ae6d622823f4abd935cbda45d0a718030a1))
* Updated Framework Version ([7841fd2](https://github.com/WebFiori/framework/commit/7841fd266b92146c4eb800adc1b859c9e022dd25))
* Updated Framework Version Number ([2f8d814](https://github.com/WebFiori/framework/commit/2f8d814fd477b3c8699ada67c2c6b47a04f29b10))
* Updated Framework Version Number ([84c7857](https://github.com/WebFiori/framework/commit/84c785722512e04c106412f3612f9ef59758ed40))
* Updated Framework Version Number ([72cb62d](https://github.com/WebFiori/framework/commit/72cb62d198cef51275c282142edacb13b1a5bcd4))
* Updated Framework Version Number ([bc89447](https://github.com/WebFiori/framework/commit/bc8944771dd1bd629f65a0df9e4067ea76e141da))
* Updated Framework Version Number ([a5177bb](https://github.com/WebFiori/framework/commit/a5177bbb1de08d1cf6748ec09ec6d9e53fa20d10))
* Updated Libraries Versions ([248d1da](https://github.com/WebFiori/framework/commit/248d1dab9efffd44ac5728d005dde58470d2fef1))
* Updated Release Please Config ([1a8b4e5](https://github.com/WebFiori/framework/commit/1a8b4e55f9a5496aac47e30228613d8a24068914))
* Updated Version Number ([280f418](https://github.com/WebFiori/framework/commit/280f418df38a2eb019b6f2a5d0c1b8b8d00133d3))
* Updated Version Number ([d75c9d0](https://github.com/WebFiori/framework/commit/d75c9d0c9547d2e4ce3edbac839a1f712a9f90a4))

## [3.0.0-Beta.20](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.19...v3.0.0-Beta.20) (2024-12-16)


### Miscellaneous Chores

* Fix target Branch ([a419a3e](https://github.com/WebFiori/framework/commit/a419a3e29bb416be328b19f1489788d51ebbcd4e))
* release 3.0.0-Beta.20 ([ddcf6b0](https://github.com/WebFiori/framework/commit/ddcf6b03010a0f011112bfa789f446b1949daaae))
* release 3.0.0-Beta.20 ([c4fc053](https://github.com/WebFiori/framework/commit/c4fc053d45a5c8b24e963a625d0954c33af2c884))
* release 3.0.0-Beta.20 ([6e72830](https://github.com/WebFiori/framework/commit/6e72830f0ec1f6943d84ee3266632d5c62e02832))
* release v3.0.0-Beta.20 ([630f512](https://github.com/WebFiori/framework/commit/630f512c6a036e111a7b146cacbd7d72dfe0da06))
* Updated Framework Version ([361e5d5](https://github.com/WebFiori/framework/commit/361e5d545a274043efceab4087d4db7769990d60))

## [3.0.0-Beta.19](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.19...v3.0.0-Beta.19) (2024-12-09)


### Features

* Added a Method to Load Multiple Files ([89d0363](https://github.com/WebFiori/framework/commit/89d0363bb81a32032e938da71a19ec959c48e2bf))
* Added a Way to Handle Configuration Errors ([76f1539](https://github.com/WebFiori/framework/commit/76f153933680c4ae4d7b067e8fca95273412ab2d))
* Added Ability to Enable or Disable Cache ([434fd72](https://github.com/WebFiori/framework/commit/434fd726657d7e4967681933bd718d60f68f2a76))
* Added Additional Logging Methods to Tasks Manager ([afc9b46](https://github.com/WebFiori/framework/commit/afc9b4697b58dbeb0cb7df26e9303fc1e720ecce))
* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Added Support for Loading Non-PSR-4 Compliant Classes ([a9772b4](https://github.com/WebFiori/framework/commit/a9772b49fc94b1a524e4555a18135461e1ef88ac))
* Added Support for Setting Env Vars Using `putenv()` ([2895d6f](https://github.com/WebFiori/framework/commit/2895d6fd7df6060ebae227867dba719864b3578a))
* Added Support for Writing Unit Test Classes for APIs ([baefa85](https://github.com/WebFiori/framework/commit/baefa855b76a7f42fb2ca0323888d0bc7d7d1f96))
* **autoloader:** Added a Method to Check Validity of Namespace ([e749a3a](https://github.com/WebFiori/framework/commit/e749a3aafa0d6c1a11da7d486cb68ad8b048b4b7))
* Automation of Writing Unit Test Cases for APIs ([5bab349](https://github.com/WebFiori/framework/commit/5bab349082328e668f13c5192dd5555b99201fa9))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Bug Fixes

* Add Missing Returns ([9dcd9bf](https://github.com/WebFiori/framework/commit/9dcd9bf2670116a514169abcfdd5af72d4b12d11))
* Added Check for Empty File Path ([b046fdf](https://github.com/WebFiori/framework/commit/b046fdf98768a63d882d102c1d20cc01b4f8a288))
* Added Handling Code for Session Serialization Errors ([a2c7955](https://github.com/WebFiori/framework/commit/a2c7955888483c4eb8e446c1b5bd8794331a174a))
* Added Missing Namespace ([069364a](https://github.com/WebFiori/framework/commit/069364a4566dc15f917ae0469fb8548ae5411771))
* **autoload:** Add File Name an NS ([eb4d5b9](https://github.com/WebFiori/framework/commit/eb4d5b93f6ea4dc12e5809a6fde63c9f2d4fa928))
* **autoload:** Check NS with Path ([a3d4c6e](https://github.com/WebFiori/framework/commit/a3d4c6e2e52eae4f6c421f533b7316b8562b1bf8))
* **cli:** Rename of Class `CommandArgument` to `Argument` ([7f67a0f](https://github.com/WebFiori/framework/commit/7f67a0f61886159261c4749955d34a4187e76cbc))
* **config:** Fix to JSON Configuration Style ([4dda36c](https://github.com/WebFiori/framework/commit/4dda36c14c8f8a77479bebb24b7b504e4bf02817))
* Correction to File Path ([df3eacf](https://github.com/WebFiori/framework/commit/df3eacfb150a43020794545f51ee1379256a46fc))
* Fix Assignment Issue ([34a522f](https://github.com/WebFiori/framework/commit/34a522ff53e8ad7bc8bc1287bc0c7595a4d7e254))
* Fix to `RunSQLQueryCommand` ([87dc2e3](https://github.com/WebFiori/framework/commit/87dc2e3a2dbf9f25dc81db2e9af9123aef198d4c))
* Fix to a Bug in Creating Test Case ([0e4b8e5](https://github.com/WebFiori/framework/commit/0e4b8e5ff0c307e45bd5fb3a2acbfacc82f9d373))
* Fix to Bug in Loading Themes ([ce67490](https://github.com/WebFiori/framework/commit/ce674903b6358824c61360a2ae335b8399c38309))
* Fix to Create CLI Command ([82c7a88](https://github.com/WebFiori/framework/commit/82c7a888a0a5140d1381b9855ecf2762eb52659b))
* Fix to Initial Namespace ([6e0e08a](https://github.com/WebFiori/framework/commit/6e0e08ace2b63c2cb959a236342014962c6a3b01))
* Fix to Line Numbers in Exception Logging ([781a233](https://github.com/WebFiori/framework/commit/781a233a9e0bdb95c4d1a40b96358d608e07de0e))
* Fix to Reading Extra Connection Props ([a6c5b92](https://github.com/WebFiori/framework/commit/a6c5b9269ac6f7a354f944f0bbc9557f6a73dd1f))
* Fix to Registering Middleware ([6cc7ce1](https://github.com/WebFiori/framework/commit/6cc7ce1e39bf0aebfe0eb5ff91360e4c4ed04f05))
* Fix to Running SQL Query from File ([0c8bb61](https://github.com/WebFiori/framework/commit/0c8bb613dbdec50c06f80ee0e4d9850602d8a71b))
* Fix to Setting Middleware Name ([3a02a60](https://github.com/WebFiori/framework/commit/3a02a60d0ed3a2decf0059ce889325fc02f64893))
* Fix to Undefined Constant ([d605a5b](https://github.com/WebFiori/framework/commit/d605a5be85cd8623a2114b8c0756372c82bd7c9b))
* Fix to Uninitialized Variable ([905c3c7](https://github.com/WebFiori/framework/commit/905c3c7b8232a8f1ee6171fa309c2489b1bdd141))
* Made `init` Static ([e04233a](https://github.com/WebFiori/framework/commit/e04233a0b4b65b903d92029dcb80ec4814dd5a08))
* Remove Unused Import ([ed43960](https://github.com/WebFiori/framework/commit/ed43960b90052084b7a95a9ac182619af1244a3f))
* **themes:** Fix to Problems in Loading Theme ([7a331ff](https://github.com/WebFiori/framework/commit/7a331ff9484fe13fcbd7a7c653321b3e9d233fba))
* **ui:** Fix to Bug In Web Page Initialization ([8645c2a](https://github.com/WebFiori/framework/commit/8645c2a024276c70a96b0ebc3b83480649bd09d7))
* **ui:** Fix to Load Language After Page Initialization ([38b0843](https://github.com/WebFiori/framework/commit/38b084385251e8b5bfdae2c8ade3ab0219bba046))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Cleanup ([0d5f798](https://github.com/WebFiori/framework/commit/0d5f7983426f7d766e79e6932ec47cf9ac7853dd))
* Code Quality Improvements ([80c7853](https://github.com/WebFiori/framework/commit/80c7853f737b16e605e11fd9bcf56f1ecc24223a))
* Code Quality Improvments ([f8e9ed9](https://github.com/WebFiori/framework/commit/f8e9ed98f1ac4b1c86cceff30a92ffd6107a05d2))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* **dev:** release 3.0.0-Beta.14 ([60aa746](https://github.com/WebFiori/framework/commit/60aa746bf39ccf7cbdda5bd9c24a6ed408d2732c))
* **dev:** release 3.0.0-Beta.14 ([8c3dd76](https://github.com/WebFiori/framework/commit/8c3dd7651f604414c5e5ccfd8567d907545d5513))
* **dev:** release 3.0.0-Beta.17 ([a3e5983](https://github.com/WebFiori/framework/commit/a3e598305d75f2fa87d6148520d88f4235a53253))
* **dev:** release 3.0.0-Beta.18 ([a994097](https://github.com/WebFiori/framework/commit/a994097c021b5575cab7f077f8d730736c3c1bbe))
* **dev:** release 3.0.0-Beta.19 ([5497825](https://github.com/WebFiori/framework/commit/549782509f23be3a90375debe0319b16e549a3aa))
* **dev:** release 3.1.0-Beta.14 ([ba5a5e3](https://github.com/WebFiori/framework/commit/ba5a5e30b4033d3f2486cbab578545aadbed67b0))
* Fix Imports ([7386f92](https://github.com/WebFiori/framework/commit/7386f9242351673588eaefe6c0de02c7e467f62a))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* release v3.0.0-Beta.17 ([3c0c639](https://github.com/WebFiori/framework/commit/3c0c639a72f9dd08bec7a150e33af2bb18e9728a))
* release v3.0.0-Beta.18 ([5a588eb](https://github.com/WebFiori/framework/commit/5a588eb52815889a8409a07a30c4e6f0defe3269))
* release v3.0.0-Beta.19 ([e8d5314](https://github.com/WebFiori/framework/commit/e8d531433f6afada6684050013b4169b3d8d547b))
* **release-please:** Added Additional Sections ([40dcfa4](https://github.com/WebFiori/framework/commit/40dcfa4bad0f8b42a34e0541ef558cd78f37b2ce))
* Remove Redeclaration ([f41549d](https://github.com/WebFiori/framework/commit/f41549da7a7570ec9984a53f16abf863a716e55d))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Run CS Fixer ([ca8e690](https://github.com/WebFiori/framework/commit/ca8e690d7e8dcc737d4fe125ea828ec4ef146035))
* Update composer.json ([819c26d](https://github.com/WebFiori/framework/commit/819c26d8fd7f23a057a76fa923b62d0a2281721d))
* Update Libraries Versions ([46f4d56](https://github.com/WebFiori/framework/commit/46f4d56aa8bc911393ed80d4e57368472dbcdd24))
* Updated .gitattribute ([63ba6d8](https://github.com/WebFiori/framework/commit/63ba6d890b82280d87d002f8c3fcfee1493ea2ff))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated CI Config ([a7175a4](https://github.com/WebFiori/framework/commit/a7175a4442cb6d5d4031d03cf228fc43439b504b))
* Updated Composer Config ([cf26913](https://github.com/WebFiori/framework/commit/cf2691382d6d883f2f06b25e789b9a30524758cd))
* Updated Core Framework Libraries ([9220fa4](https://github.com/WebFiori/framework/commit/9220fa4c77c668793962afc427495adcd6c8ca55))
* Updated Core Libraries ([c21a48f](https://github.com/WebFiori/framework/commit/c21a48f6586068b4cab3c223465e4b3c60849752))
* Updated Core Libraries ([fda39a9](https://github.com/WebFiori/framework/commit/fda39a9168b6e8cebda1408e0de4f0f3815845f5))
* Updated Core Libraries ([4aa9670](https://github.com/WebFiori/framework/commit/4aa96707feabd9518a788d4393442b0287f0a375))
* Updated Core Libraries Versions ([dcb1a15](https://github.com/WebFiori/framework/commit/dcb1a15882cac069cb7439101b8e096018c1cfc1))
* Updated Core Library Version ([db4d223](https://github.com/WebFiori/framework/commit/db4d223a4cb2b8bc40a45fa69e4d67b358d1f29a))
* Updated Dependences ([a160f0f](https://github.com/WebFiori/framework/commit/a160f0fcc7cb0b2570c9487090b2bcc3e0ad658e))
* Updated Dependencies ([e48f333](https://github.com/WebFiori/framework/commit/e48f3336d8c0b1910e18b8baa5ea40be28c9e50d))
* Updated Dependencies ([8284dc6](https://github.com/WebFiori/framework/commit/8284dc655e8e92aafc3fb6a1bd88861254da0fe1))
* Updated Dependencies ([97bb7a2](https://github.com/WebFiori/framework/commit/97bb7a220a9c8a81fd70a4c2d80d891e4f4c7eb2))
* Updated Dependencies ([aef4319](https://github.com/WebFiori/framework/commit/aef4319b1dd10cd4f208e943e638b4b364b04cd0))
* Updated Dependencies Version ([0d3ead5](https://github.com/WebFiori/framework/commit/0d3ead5cad177efd50e4b285222fcdbaf8beab66))
* Updated Dependencies Versions ([07252d0](https://github.com/WebFiori/framework/commit/07252d09f40af27cc04494ea7081a41fe0fe2ede))
* Updated Errors Handling Library ([5cf44a9](https://github.com/WebFiori/framework/commit/5cf44a9b5ecae3ac5ed3888c18c33e5415055703))
* Updated Framework Version ([783f4be](https://github.com/WebFiori/framework/commit/783f4be57869ae93eab8c0b49fe2ede5cc7fbba8))
* Updated Framework Version ([0403027](https://github.com/WebFiori/framework/commit/0403027fc02bfbba13dd1c899ae073f43c925cd8))
* Updated Framework Version ([f7c0f7f](https://github.com/WebFiori/framework/commit/f7c0f7f0dad2900d988b3866c2a035b0b1c10e7b))
* Updated Framework Version ([d44cedc](https://github.com/WebFiori/framework/commit/d44cedc29ce9097403bdb263c279812f78d7581b))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
* Updated Framework Version ([fb91c15](https://github.com/WebFiori/framework/commit/fb91c15587fb006f096b90a2f2b01e5b9ffb7c47))
* Updated Framework Version ([a817e8f](https://github.com/WebFiori/framework/commit/a817e8feec235cec29e6c20e00732a25d1c80534))
* Updated Framework Version ([1672faf](https://github.com/WebFiori/framework/commit/1672faf3f12ef217915d5ea62d6fae9e01c9db28))
* Updated Framework Version ([8b013ae](https://github.com/WebFiori/framework/commit/8b013ae6d622823f4abd935cbda45d0a718030a1))
* Updated Framework Version ([7841fd2](https://github.com/WebFiori/framework/commit/7841fd266b92146c4eb800adc1b859c9e022dd25))
* Updated Framework Version Number ([2f8d814](https://github.com/WebFiori/framework/commit/2f8d814fd477b3c8699ada67c2c6b47a04f29b10))
* Updated Framework Version Number ([84c7857](https://github.com/WebFiori/framework/commit/84c785722512e04c106412f3612f9ef59758ed40))
* Updated Framework Version Number ([72cb62d](https://github.com/WebFiori/framework/commit/72cb62d198cef51275c282142edacb13b1a5bcd4))
* Updated Framework Version Number ([bc89447](https://github.com/WebFiori/framework/commit/bc8944771dd1bd629f65a0df9e4067ea76e141da))
* Updated Framework Version Number ([a5177bb](https://github.com/WebFiori/framework/commit/a5177bbb1de08d1cf6748ec09ec6d9e53fa20d10))
* Updated Release Please Config ([1a8b4e5](https://github.com/WebFiori/framework/commit/1a8b4e55f9a5496aac47e30228613d8a24068914))
* Updated Version Number ([280f418](https://github.com/WebFiori/framework/commit/280f418df38a2eb019b6f2a5d0c1b8b8d00133d3))
* Updated Version Number ([d75c9d0](https://github.com/WebFiori/framework/commit/d75c9d0c9547d2e4ce3edbac839a1f712a9f90a4))

## [3.0.0-Beta.19](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.18...v3.0.0-Beta.19) (2024-12-09)


### Miscellaneous Chores

* release v3.0.0-Beta.19 ([e8d5314](https://github.com/WebFiori/framework/commit/e8d531433f6afada6684050013b4169b3d8d547b))
* Updated Dependencies ([8284dc6](https://github.com/WebFiori/framework/commit/8284dc655e8e92aafc3fb6a1bd88861254da0fe1))
* Updated Version Number ([280f418](https://github.com/WebFiori/framework/commit/280f418df38a2eb019b6f2a5d0c1b8b8d00133d3))

## [3.0.0-Beta.18](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.17...v3.0.0-Beta.18) (2024-12-04)


### Bug Fixes

* Correction to File Path ([df3eacf](https://github.com/WebFiori/framework/commit/df3eacfb150a43020794545f51ee1379256a46fc))
* Fix to Undefined Constant ([d605a5b](https://github.com/WebFiori/framework/commit/d605a5be85cd8623a2114b8c0756372c82bd7c9b))


### Miscellaneous Chores

* release v3.0.0-Beta.18 ([5a588eb](https://github.com/WebFiori/framework/commit/5a588eb52815889a8409a07a30c4e6f0defe3269))
* Updated Framework Version ([783f4be](https://github.com/WebFiori/framework/commit/783f4be57869ae93eab8c0b49fe2ede5cc7fbba8))
* Updated Release Please Config ([1a8b4e5](https://github.com/WebFiori/framework/commit/1a8b4e55f9a5496aac47e30228613d8a24068914))

## [3.0.0-Beta.17](https://github.com/WebFiori/framework/compare/v3.1.0-Beta.14...v3.0.0-Beta.17) (2024-12-03)


### Features

* Added a Method to Load Multiple Files ([89d0363](https://github.com/WebFiori/framework/commit/89d0363bb81a32032e938da71a19ec959c48e2bf))
* Added a Way to Handle Configuration Errors ([76f1539](https://github.com/WebFiori/framework/commit/76f153933680c4ae4d7b067e8fca95273412ab2d))
* Added Ability to Enable or Disable Cache ([434fd72](https://github.com/WebFiori/framework/commit/434fd726657d7e4967681933bd718d60f68f2a76))
* Added Additional Logging Methods to Tasks Manager ([afc9b46](https://github.com/WebFiori/framework/commit/afc9b4697b58dbeb0cb7df26e9303fc1e720ecce))
* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Added Support for Loading Non-PSR-4 Compliant Classes ([a9772b4](https://github.com/WebFiori/framework/commit/a9772b49fc94b1a524e4555a18135461e1ef88ac))
* Added Support for Setting Env Vars Using `putenv()` ([2895d6f](https://github.com/WebFiori/framework/commit/2895d6fd7df6060ebae227867dba719864b3578a))
* Added Support for Writing Unit Test Classes for APIs ([baefa85](https://github.com/WebFiori/framework/commit/baefa855b76a7f42fb2ca0323888d0bc7d7d1f96))
* **autoloader:** Added a Method to Check Validity of Namespace ([e749a3a](https://github.com/WebFiori/framework/commit/e749a3aafa0d6c1a11da7d486cb68ad8b048b4b7))
* Automation of Writing Unit Test Cases for APIs ([5bab349](https://github.com/WebFiori/framework/commit/5bab349082328e668f13c5192dd5555b99201fa9))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Bug Fixes

* Add Missing Returns ([9dcd9bf](https://github.com/WebFiori/framework/commit/9dcd9bf2670116a514169abcfdd5af72d4b12d11))
* Added Check for Empty File Path ([b046fdf](https://github.com/WebFiori/framework/commit/b046fdf98768a63d882d102c1d20cc01b4f8a288))
* Added Handling Code for Session Serialization Errors ([a2c7955](https://github.com/WebFiori/framework/commit/a2c7955888483c4eb8e446c1b5bd8794331a174a))
* Added Missing Namespace ([069364a](https://github.com/WebFiori/framework/commit/069364a4566dc15f917ae0469fb8548ae5411771))
* **autoload:** Add File Name an NS ([eb4d5b9](https://github.com/WebFiori/framework/commit/eb4d5b93f6ea4dc12e5809a6fde63c9f2d4fa928))
* **autoload:** Check NS with Path ([a3d4c6e](https://github.com/WebFiori/framework/commit/a3d4c6e2e52eae4f6c421f533b7316b8562b1bf8))
* **cli:** Rename of Class `CommandArgument` to `Argument` ([7f67a0f](https://github.com/WebFiori/framework/commit/7f67a0f61886159261c4749955d34a4187e76cbc))
* **config:** Fix to JSON Configuration Style ([4dda36c](https://github.com/WebFiori/framework/commit/4dda36c14c8f8a77479bebb24b7b504e4bf02817))
* Fix to `RunSQLQueryCommand` ([87dc2e3](https://github.com/WebFiori/framework/commit/87dc2e3a2dbf9f25dc81db2e9af9123aef198d4c))
* Fix to a Bug in Creating Test Case ([0e4b8e5](https://github.com/WebFiori/framework/commit/0e4b8e5ff0c307e45bd5fb3a2acbfacc82f9d373))
* Fix to Bug in Loading Themes ([ce67490](https://github.com/WebFiori/framework/commit/ce674903b6358824c61360a2ae335b8399c38309))
* Fix to Create CLI Command ([82c7a88](https://github.com/WebFiori/framework/commit/82c7a888a0a5140d1381b9855ecf2762eb52659b))
* Fix to Initial Namespace ([6e0e08a](https://github.com/WebFiori/framework/commit/6e0e08ace2b63c2cb959a236342014962c6a3b01))
* Fix to Line Numbers in Exception Logging ([781a233](https://github.com/WebFiori/framework/commit/781a233a9e0bdb95c4d1a40b96358d608e07de0e))
* Fix to Reading Extra Connection Props ([a6c5b92](https://github.com/WebFiori/framework/commit/a6c5b9269ac6f7a354f944f0bbc9557f6a73dd1f))
* Fix to Running SQL Query from File ([0c8bb61](https://github.com/WebFiori/framework/commit/0c8bb613dbdec50c06f80ee0e4d9850602d8a71b))
* Fix to Setting Middleware Name ([3a02a60](https://github.com/WebFiori/framework/commit/3a02a60d0ed3a2decf0059ce889325fc02f64893))
* Fix to Uninitialized Variable ([905c3c7](https://github.com/WebFiori/framework/commit/905c3c7b8232a8f1ee6171fa309c2489b1bdd141))
* Made `init` Static ([e04233a](https://github.com/WebFiori/framework/commit/e04233a0b4b65b903d92029dcb80ec4814dd5a08))
* Remove Unused Import ([ed43960](https://github.com/WebFiori/framework/commit/ed43960b90052084b7a95a9ac182619af1244a3f))
* **themes:** Fix to Problems in Loading Theme ([7a331ff](https://github.com/WebFiori/framework/commit/7a331ff9484fe13fcbd7a7c653321b3e9d233fba))
* **ui:** Fix to Bug In Web Page Initialization ([8645c2a](https://github.com/WebFiori/framework/commit/8645c2a024276c70a96b0ebc3b83480649bd09d7))
* **ui:** Fix to Load Language After Page Initialization ([38b0843](https://github.com/WebFiori/framework/commit/38b084385251e8b5bfdae2c8ade3ab0219bba046))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Cleanup ([0d5f798](https://github.com/WebFiori/framework/commit/0d5f7983426f7d766e79e6932ec47cf9ac7853dd))
* Code Quality Improvements ([80c7853](https://github.com/WebFiori/framework/commit/80c7853f737b16e605e11fd9bcf56f1ecc24223a))
* Code Quality Improvments ([f8e9ed9](https://github.com/WebFiori/framework/commit/f8e9ed98f1ac4b1c86cceff30a92ffd6107a05d2))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* **dev:** release 3.0.0-Beta.14 ([60aa746](https://github.com/WebFiori/framework/commit/60aa746bf39ccf7cbdda5bd9c24a6ed408d2732c))
* **dev:** release 3.0.0-Beta.14 ([8c3dd76](https://github.com/WebFiori/framework/commit/8c3dd7651f604414c5e5ccfd8567d907545d5513))
* **dev:** release 3.1.0-Beta.14 ([ba5a5e3](https://github.com/WebFiori/framework/commit/ba5a5e30b4033d3f2486cbab578545aadbed67b0))
* Fix Imports ([7386f92](https://github.com/WebFiori/framework/commit/7386f9242351673588eaefe6c0de02c7e467f62a))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* release v3.0.0-Beta.17 ([3c0c639](https://github.com/WebFiori/framework/commit/3c0c639a72f9dd08bec7a150e33af2bb18e9728a))
* **release-please:** Added Additional Sections ([40dcfa4](https://github.com/WebFiori/framework/commit/40dcfa4bad0f8b42a34e0541ef558cd78f37b2ce))
* Remove Redeclaration ([f41549d](https://github.com/WebFiori/framework/commit/f41549da7a7570ec9984a53f16abf863a716e55d))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Run CS Fixer ([ca8e690](https://github.com/WebFiori/framework/commit/ca8e690d7e8dcc737d4fe125ea828ec4ef146035))
* Update composer.json ([819c26d](https://github.com/WebFiori/framework/commit/819c26d8fd7f23a057a76fa923b62d0a2281721d))
* Update Libraries Versions ([46f4d56](https://github.com/WebFiori/framework/commit/46f4d56aa8bc911393ed80d4e57368472dbcdd24))
* Updated .gitattribute ([63ba6d8](https://github.com/WebFiori/framework/commit/63ba6d890b82280d87d002f8c3fcfee1493ea2ff))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated CI Config ([a7175a4](https://github.com/WebFiori/framework/commit/a7175a4442cb6d5d4031d03cf228fc43439b504b))
* Updated Composer Config ([cf26913](https://github.com/WebFiori/framework/commit/cf2691382d6d883f2f06b25e789b9a30524758cd))
* Updated Core Framework Libraries ([9220fa4](https://github.com/WebFiori/framework/commit/9220fa4c77c668793962afc427495adcd6c8ca55))
* Updated Core Libraries ([c21a48f](https://github.com/WebFiori/framework/commit/c21a48f6586068b4cab3c223465e4b3c60849752))
* Updated Core Libraries ([fda39a9](https://github.com/WebFiori/framework/commit/fda39a9168b6e8cebda1408e0de4f0f3815845f5))
* Updated Core Libraries ([4aa9670](https://github.com/WebFiori/framework/commit/4aa96707feabd9518a788d4393442b0287f0a375))
* Updated Core Libraries Versions ([dcb1a15](https://github.com/WebFiori/framework/commit/dcb1a15882cac069cb7439101b8e096018c1cfc1))
* Updated Core Library Version ([db4d223](https://github.com/WebFiori/framework/commit/db4d223a4cb2b8bc40a45fa69e4d67b358d1f29a))
* Updated Dependences ([a160f0f](https://github.com/WebFiori/framework/commit/a160f0fcc7cb0b2570c9487090b2bcc3e0ad658e))
* Updated Dependencies ([97bb7a2](https://github.com/WebFiori/framework/commit/97bb7a220a9c8a81fd70a4c2d80d891e4f4c7eb2))
* Updated Dependencies ([aef4319](https://github.com/WebFiori/framework/commit/aef4319b1dd10cd4f208e943e638b4b364b04cd0))
* Updated Dependencies Version ([0d3ead5](https://github.com/WebFiori/framework/commit/0d3ead5cad177efd50e4b285222fcdbaf8beab66))
* Updated Dependencies Versions ([07252d0](https://github.com/WebFiori/framework/commit/07252d09f40af27cc04494ea7081a41fe0fe2ede))
* Updated Errors Handling Library ([5cf44a9](https://github.com/WebFiori/framework/commit/5cf44a9b5ecae3ac5ed3888c18c33e5415055703))
* Updated Framework Version ([0403027](https://github.com/WebFiori/framework/commit/0403027fc02bfbba13dd1c899ae073f43c925cd8))
* Updated Framework Version ([f7c0f7f](https://github.com/WebFiori/framework/commit/f7c0f7f0dad2900d988b3866c2a035b0b1c10e7b))
* Updated Framework Version ([d44cedc](https://github.com/WebFiori/framework/commit/d44cedc29ce9097403bdb263c279812f78d7581b))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
* Updated Framework Version ([fb91c15](https://github.com/WebFiori/framework/commit/fb91c15587fb006f096b90a2f2b01e5b9ffb7c47))
* Updated Framework Version ([a817e8f](https://github.com/WebFiori/framework/commit/a817e8feec235cec29e6c20e00732a25d1c80534))
* Updated Framework Version ([1672faf](https://github.com/WebFiori/framework/commit/1672faf3f12ef217915d5ea62d6fae9e01c9db28))
* Updated Framework Version ([8b013ae](https://github.com/WebFiori/framework/commit/8b013ae6d622823f4abd935cbda45d0a718030a1))
* Updated Framework Version ([7841fd2](https://github.com/WebFiori/framework/commit/7841fd266b92146c4eb800adc1b859c9e022dd25))
* Updated Framework Version Number ([2f8d814](https://github.com/WebFiori/framework/commit/2f8d814fd477b3c8699ada67c2c6b47a04f29b10))
* Updated Framework Version Number ([84c7857](https://github.com/WebFiori/framework/commit/84c785722512e04c106412f3612f9ef59758ed40))
* Updated Framework Version Number ([72cb62d](https://github.com/WebFiori/framework/commit/72cb62d198cef51275c282142edacb13b1a5bcd4))
* Updated Framework Version Number ([bc89447](https://github.com/WebFiori/framework/commit/bc8944771dd1bd629f65a0df9e4067ea76e141da))
* Updated Framework Version Number ([a5177bb](https://github.com/WebFiori/framework/commit/a5177bbb1de08d1cf6748ec09ec6d9e53fa20d10))
* Updated Version Number ([d75c9d0](https://github.com/WebFiori/framework/commit/d75c9d0c9547d2e4ce3edbac839a1f712a9f90a4))

## [3.1.0-Beta.14](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.14...v3.1.0-Beta.14) (2024-11-27)


### Features

* Added Ability to Enable or Disable Cache ([434fd72](https://github.com/WebFiori/framework/commit/434fd726657d7e4967681933bd718d60f68f2a76))


### Miscellaneous Chores

* Updated Dependencies Versions ([07252d0](https://github.com/WebFiori/framework/commit/07252d09f40af27cc04494ea7081a41fe0fe2ede))
* Updated Framework Version ([d44cedc](https://github.com/WebFiori/framework/commit/d44cedc29ce9097403bdb263c279812f78d7581b))

## [3.0.0-Beta.14](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.14...v3.0.0-Beta.14) (2024-11-21)


### Features

* Added a Method to Load Multiple Files ([89d0363](https://github.com/WebFiori/framework/commit/89d0363bb81a32032e938da71a19ec959c48e2bf))
* Added a Way to Handle Configuration Errors ([76f1539](https://github.com/WebFiori/framework/commit/76f153933680c4ae4d7b067e8fca95273412ab2d))
* Added Additional Logging Methods to Tasks Manager ([afc9b46](https://github.com/WebFiori/framework/commit/afc9b4697b58dbeb0cb7df26e9303fc1e720ecce))
* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Added Support for Loading Non-PSR-4 Compliant Classes ([a9772b4](https://github.com/WebFiori/framework/commit/a9772b49fc94b1a524e4555a18135461e1ef88ac))
* Added Support for Setting Env Vars Using `putenv()` ([2895d6f](https://github.com/WebFiori/framework/commit/2895d6fd7df6060ebae227867dba719864b3578a))
* Added Support for Writing Unit Test Classes for APIs ([baefa85](https://github.com/WebFiori/framework/commit/baefa855b76a7f42fb2ca0323888d0bc7d7d1f96))
* **autoloader:** Added a Method to Check Validity of Namespace ([e749a3a](https://github.com/WebFiori/framework/commit/e749a3aafa0d6c1a11da7d486cb68ad8b048b4b7))
* Automation of Writing Unit Test Cases for APIs ([5bab349](https://github.com/WebFiori/framework/commit/5bab349082328e668f13c5192dd5555b99201fa9))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Bug Fixes

* Add Missing Returns ([9dcd9bf](https://github.com/WebFiori/framework/commit/9dcd9bf2670116a514169abcfdd5af72d4b12d11))
* Added Check for Empty File Path ([b046fdf](https://github.com/WebFiori/framework/commit/b046fdf98768a63d882d102c1d20cc01b4f8a288))
* Added Handling Code for Session Serialization Errors ([a2c7955](https://github.com/WebFiori/framework/commit/a2c7955888483c4eb8e446c1b5bd8794331a174a))
* Added Missing Namespace ([069364a](https://github.com/WebFiori/framework/commit/069364a4566dc15f917ae0469fb8548ae5411771))
* **autoload:** Add File Name an NS ([eb4d5b9](https://github.com/WebFiori/framework/commit/eb4d5b93f6ea4dc12e5809a6fde63c9f2d4fa928))
* **autoload:** Check NS with Path ([a3d4c6e](https://github.com/WebFiori/framework/commit/a3d4c6e2e52eae4f6c421f533b7316b8562b1bf8))
* **cli:** Rename of Class `CommandArgument` to `Argument` ([7f67a0f](https://github.com/WebFiori/framework/commit/7f67a0f61886159261c4749955d34a4187e76cbc))
* **config:** Fix to JSON Configuration Style ([4dda36c](https://github.com/WebFiori/framework/commit/4dda36c14c8f8a77479bebb24b7b504e4bf02817))
* Fix to `RunSQLQueryCommand` ([87dc2e3](https://github.com/WebFiori/framework/commit/87dc2e3a2dbf9f25dc81db2e9af9123aef198d4c))
* Fix to a Bug in Creating Test Case ([0e4b8e5](https://github.com/WebFiori/framework/commit/0e4b8e5ff0c307e45bd5fb3a2acbfacc82f9d373))
* Fix to Bug in Loading Themes ([ce67490](https://github.com/WebFiori/framework/commit/ce674903b6358824c61360a2ae335b8399c38309))
* Fix to Create CLI Command ([82c7a88](https://github.com/WebFiori/framework/commit/82c7a888a0a5140d1381b9855ecf2762eb52659b))
* Fix to Initial Namespace ([6e0e08a](https://github.com/WebFiori/framework/commit/6e0e08ace2b63c2cb959a236342014962c6a3b01))
* Fix to Line Numbers in Exception Logging ([781a233](https://github.com/WebFiori/framework/commit/781a233a9e0bdb95c4d1a40b96358d608e07de0e))
* Fix to Reading Extra Connection Props ([a6c5b92](https://github.com/WebFiori/framework/commit/a6c5b9269ac6f7a354f944f0bbc9557f6a73dd1f))
* Fix to Running SQL Query from File ([0c8bb61](https://github.com/WebFiori/framework/commit/0c8bb613dbdec50c06f80ee0e4d9850602d8a71b))
* Fix to Setting Middleware Name ([3a02a60](https://github.com/WebFiori/framework/commit/3a02a60d0ed3a2decf0059ce889325fc02f64893))
* Fix to Uninitialized Variable ([905c3c7](https://github.com/WebFiori/framework/commit/905c3c7b8232a8f1ee6171fa309c2489b1bdd141))
* Made `init` Static ([e04233a](https://github.com/WebFiori/framework/commit/e04233a0b4b65b903d92029dcb80ec4814dd5a08))
* Remove Unused Import ([ed43960](https://github.com/WebFiori/framework/commit/ed43960b90052084b7a95a9ac182619af1244a3f))
* **themes:** Fix to Problems in Loading Theme ([7a331ff](https://github.com/WebFiori/framework/commit/7a331ff9484fe13fcbd7a7c653321b3e9d233fba))
* **ui:** Fix to Bug In Web Page Initialization ([8645c2a](https://github.com/WebFiori/framework/commit/8645c2a024276c70a96b0ebc3b83480649bd09d7))
* **ui:** Fix to Load Language After Page Initialization ([38b0843](https://github.com/WebFiori/framework/commit/38b084385251e8b5bfdae2c8ade3ab0219bba046))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Cleanup ([0d5f798](https://github.com/WebFiori/framework/commit/0d5f7983426f7d766e79e6932ec47cf9ac7853dd))
* Code Quality Improvements ([80c7853](https://github.com/WebFiori/framework/commit/80c7853f737b16e605e11fd9bcf56f1ecc24223a))
* Code Quality Improvments ([f8e9ed9](https://github.com/WebFiori/framework/commit/f8e9ed98f1ac4b1c86cceff30a92ffd6107a05d2))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* **dev:** release 3.0.0-Beta.14 ([8c3dd76](https://github.com/WebFiori/framework/commit/8c3dd7651f604414c5e5ccfd8567d907545d5513))
* Fix Imports ([7386f92](https://github.com/WebFiori/framework/commit/7386f9242351673588eaefe6c0de02c7e467f62a))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* **release-please:** Added Additional Sections ([40dcfa4](https://github.com/WebFiori/framework/commit/40dcfa4bad0f8b42a34e0541ef558cd78f37b2ce))
* Remove Redeclaration ([f41549d](https://github.com/WebFiori/framework/commit/f41549da7a7570ec9984a53f16abf863a716e55d))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Run CS Fixer ([ca8e690](https://github.com/WebFiori/framework/commit/ca8e690d7e8dcc737d4fe125ea828ec4ef146035))
* Update composer.json ([819c26d](https://github.com/WebFiori/framework/commit/819c26d8fd7f23a057a76fa923b62d0a2281721d))
* Updated .gitattribute ([63ba6d8](https://github.com/WebFiori/framework/commit/63ba6d890b82280d87d002f8c3fcfee1493ea2ff))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated CI Config ([a7175a4](https://github.com/WebFiori/framework/commit/a7175a4442cb6d5d4031d03cf228fc43439b504b))
* Updated Composer Config ([cf26913](https://github.com/WebFiori/framework/commit/cf2691382d6d883f2f06b25e789b9a30524758cd))
* Updated Core Framework Libraries ([9220fa4](https://github.com/WebFiori/framework/commit/9220fa4c77c668793962afc427495adcd6c8ca55))
* Updated Core Libraries ([c21a48f](https://github.com/WebFiori/framework/commit/c21a48f6586068b4cab3c223465e4b3c60849752))
* Updated Core Libraries ([fda39a9](https://github.com/WebFiori/framework/commit/fda39a9168b6e8cebda1408e0de4f0f3815845f5))
* Updated Core Libraries ([4aa9670](https://github.com/WebFiori/framework/commit/4aa96707feabd9518a788d4393442b0287f0a375))
* Updated Core Libraries Versions ([dcb1a15](https://github.com/WebFiori/framework/commit/dcb1a15882cac069cb7439101b8e096018c1cfc1))
* Updated Core Library Version ([db4d223](https://github.com/WebFiori/framework/commit/db4d223a4cb2b8bc40a45fa69e4d67b358d1f29a))
* Updated Dependencies ([aef4319](https://github.com/WebFiori/framework/commit/aef4319b1dd10cd4f208e943e638b4b364b04cd0))
* Updated Dependencies Version ([0d3ead5](https://github.com/WebFiori/framework/commit/0d3ead5cad177efd50e4b285222fcdbaf8beab66))
* Updated Errors Handling Library ([5cf44a9](https://github.com/WebFiori/framework/commit/5cf44a9b5ecae3ac5ed3888c18c33e5415055703))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
* Updated Framework Version ([fb91c15](https://github.com/WebFiori/framework/commit/fb91c15587fb006f096b90a2f2b01e5b9ffb7c47))
* Updated Framework Version ([a817e8f](https://github.com/WebFiori/framework/commit/a817e8feec235cec29e6c20e00732a25d1c80534))
* Updated Framework Version ([1672faf](https://github.com/WebFiori/framework/commit/1672faf3f12ef217915d5ea62d6fae9e01c9db28))
* Updated Framework Version ([8b013ae](https://github.com/WebFiori/framework/commit/8b013ae6d622823f4abd935cbda45d0a718030a1))
* Updated Framework Version ([7841fd2](https://github.com/WebFiori/framework/commit/7841fd266b92146c4eb800adc1b859c9e022dd25))
* Updated Framework Version Number ([2f8d814](https://github.com/WebFiori/framework/commit/2f8d814fd477b3c8699ada67c2c6b47a04f29b10))
* Updated Framework Version Number ([84c7857](https://github.com/WebFiori/framework/commit/84c785722512e04c106412f3612f9ef59758ed40))
* Updated Framework Version Number ([72cb62d](https://github.com/WebFiori/framework/commit/72cb62d198cef51275c282142edacb13b1a5bcd4))
* Updated Framework Version Number ([bc89447](https://github.com/WebFiori/framework/commit/bc8944771dd1bd629f65a0df9e4067ea76e141da))
* Updated Framework Version Number ([a5177bb](https://github.com/WebFiori/framework/commit/a5177bbb1de08d1cf6748ec09ec6d9e53fa20d10))
* Updated Version Number ([d75c9d0](https://github.com/WebFiori/framework/commit/d75c9d0c9547d2e4ce3edbac839a1f712a9f90a4))

## [3.0.0-Beta.14](https://github.com/WebFiori/framework/compare/v3.0.0-Beta.13...v3.0.0-Beta.14) (2024-11-21)


### Features

* Added More Abstraction to Cache Feature ([f51b7b9](https://github.com/WebFiori/framework/commit/f51b7b9d74ef992625a697faa09e71e1c7873f22))
* Caching (Initial Prototype) ([4a063f3](https://github.com/WebFiori/framework/commit/4a063f3b1070b04bf81adf1ac2ea2089002adf84))
* Routes Caching ([bbbacff](https://github.com/WebFiori/framework/commit/bbbacffd93174662a6359dc3b6c51a3e1db74dd6))


### Miscellaneous Chores

* Added Documentation ([697155f](https://github.com/WebFiori/framework/commit/697155f3904a7fbaac37421bc0b75e31d1fd932a))
* Added Please Release Manifest and Config ([25970da](https://github.com/WebFiori/framework/commit/25970da8ea98c77a3bf9dd44ae443e8fc5cbb7c6))
* Added Release Please Config & Manifest ([3b6273c](https://github.com/WebFiori/framework/commit/3b6273c644189f8e52a22b38041921eeab15c7f3))
* Added Release Please to Workflow ([6da66a3](https://github.com/WebFiori/framework/commit/6da66a3eed187878aaa5557765537e65a9f00853))
* Change Target Branch for Release Please ([452b9ff](https://github.com/WebFiori/framework/commit/452b9ff4f3919d6416c4ce55316a5b1325482437))
* Configuration for Please Release ([33caa13](https://github.com/WebFiori/framework/commit/33caa13908911242236e7f22e7ce603f41c63207))
* release 3.0.0-Beta.14 ([872a0ec](https://github.com/WebFiori/framework/commit/872a0ec0cf732dbe1e2ef3e11d51d79d68b2fb8b))
* Remove Unused Imports ([53288a9](https://github.com/WebFiori/framework/commit/53288a9063a672bb37da06e6d6e15a492d57b45b))
* Run CS Fixer ([13f2dde](https://github.com/WebFiori/framework/commit/13f2dde9bc289ea682a045a8c8ab10c7edaf8891))
* Updated CI Config ([2f14e35](https://github.com/WebFiori/framework/commit/2f14e354fb6d0017197def88049e71e7a3f46f95))
* Updated Framework Version ([f27a583](https://github.com/WebFiori/framework/commit/f27a583ffa12f4d8aecb5682a2e58c78f191c095))
