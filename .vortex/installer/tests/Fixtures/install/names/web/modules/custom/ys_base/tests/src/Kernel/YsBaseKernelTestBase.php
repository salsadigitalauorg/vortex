@@ -2,22 +2,22 @@
 
 declare(strict_types=1);
 
-namespace Drupal\Tests\sw_core\Kernel;
+namespace Drupal\Tests\the_force_core\Kernel;
 
 use Drupal\KernelTests\KernelTestBase;
-use Drupal\Tests\sw_core\Traits\ArrayTrait;
-use Drupal\Tests\sw_core\Traits\AssertTrait;
-use Drupal\Tests\sw_core\Traits\MockTrait;
-use Drupal\Tests\sw_core\Traits\ReflectionTrait;
+use Drupal\Tests\the_force_core\Traits\ArrayTrait;
+use Drupal\Tests\the_force_core\Traits\AssertTrait;
+use Drupal\Tests\the_force_core\Traits\MockTrait;
+use Drupal\Tests\the_force_core\Traits\ReflectionTrait;
 
 /**
- * Class SwCoreKernelTestBase.
+ * Class TheForceCoreKernelTestBase.
  *
  * Base class for kernel tests.
  *
- * @package Drupal\sw_core\Tests
+ * @package Drupal\the_force_core\Tests
  */
-abstract class SwCoreKernelTestBase extends KernelTestBase {
+abstract class TheForceCoreKernelTestBase extends KernelTestBase {
 
   use ArrayTrait;
   use AssertTrait;
