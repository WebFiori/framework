# Changelog

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
