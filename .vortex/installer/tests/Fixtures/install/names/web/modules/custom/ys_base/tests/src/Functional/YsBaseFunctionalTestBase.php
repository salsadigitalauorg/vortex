@@ -2,22 +2,22 @@
 
 declare(strict_types=1);
 
-namespace Drupal\Tests\sw_core\Functional;
+namespace Drupal\Tests\the_force_core\Functional;
 
 use Drupal\Tests\BrowserTestBase;
-use Drupal\Tests\sw_core\Traits\ArrayTrait;
-use Drupal\Tests\sw_core\Traits\AssertTrait;
-use Drupal\Tests\sw_core\Traits\MockTrait;
-use Drupal\Tests\sw_core\Traits\ReflectionTrait;
+use Drupal\Tests\the_force_core\Traits\ArrayTrait;
+use Drupal\Tests\the_force_core\Traits\AssertTrait;
+use Drupal\Tests\the_force_core\Traits\MockTrait;
+use Drupal\Tests\the_force_core\Traits\ReflectionTrait;
 
 /**
- * Class SwCoreFunctionalTestBase.
+ * Class TheForceCoreFunctionalTestBase.
  *
  * Base class for functional tests.
  *
- * @package Drupal\sw_core\Tests
+ * @package Drupal\the_force_core\Tests
  */
-abstract class SwCoreFunctionalTestBase extends BrowserTestBase {
+abstract class TheForceCoreFunctionalTestBase extends BrowserTestBase {
 
   use ArrayTrait;
   use AssertTrait;
