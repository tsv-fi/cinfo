<?php

/**
 * @defgroup plugins_generic_cinfo cinfo Plugin
 */

/**
 * @file plugins/generic/cinfo/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_cinfo
 * @brief Wrapper for cinfo plugin.
 *
 */

require_once('CinfoPlugin.inc.php');

return new CinfoPlugin();

?>
