@@ -14,8 +14,8 @@
  *
  * @codeCoverageIgnore
  */
-function sw_core_deploy_install_theme(): void {
+function the_force_core_deploy_install_theme(): void {
   \Drupal::service('theme_installer')->install(['olivero']);
-  \Drupal::service('theme_installer')->install(['star_wars']);
-  \Drupal::service('config.factory')->getEditable('system.theme')->set('default', 'star_wars')->save();
+  \Drupal::service('theme_installer')->install(['lightsaber']);
+  \Drupal::service('config.factory')->getEditable('system.theme')->set('default', 'lightsaber')->save();
 }
