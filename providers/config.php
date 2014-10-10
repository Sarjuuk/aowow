<?php
  global $config;

  /// Directory where MPQ files were unpacked:
  $config['mpq'] = "data/";

  /// Directory where to extract icons
  //$config['icons'] = "../images/icons/";
  $config['icons'] = "icons/";

  /// Directory where to extract maps
  //$config['maps'] = "../images/maps/enus/";
  $config['maps'] = "maps/";

  /// [optional] Directory where to generate maps masks
  // Commenting it out will make map generation faster
  //$config['tmp'] = "../images/tmp/";
  //$config['tmp'] = "tmp/";

  /// Path to DBC files extracted from english client (for aowow_sql.php)
  $config['english_dbc'] = "data/DBFilesClient/";

  /// Path to DBC files extracted from localized client (for aowow_sql_loc.php)
  $config['local_dbc'] = "data/DBFilesClient/";

  /// Locale ID. Used only by aowow_sql_loc.php.
  $config['locale'] = 0; // (2 - french, 3 - german, 8 - russian)
?>
