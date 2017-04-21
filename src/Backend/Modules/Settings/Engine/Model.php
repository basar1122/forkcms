<?php

namespace Backend\Modules\Settings\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Model as BackendModel;

/**
 * In this file we store all generic functions that we will be using in the settings module.
 */
class Model
{
    /**
     * Get warnings for active modules
     *
     * @return array
     */
    public static function getWarnings(): array
    {
        // init vars
        $warnings = [];
        $installedModules = BackendModel::getModules();

        // loop modules
        foreach ($installedModules as $module) {
            // model class
            $class = 'Backend\\Modules\\' . $module . '\\Engine\\Model';
            if ($module == 'Core') {
                $class = 'Backend\\Core\\Engine\\Model';
            }

            // method exists
            if (is_callable([$class, 'checkSettings'])) {
                // add possible warnings
                $warnings = array_merge($warnings, call_user_func([$class, 'checkSettings']));
            }
        }

        // Multiple modules can return the same errors.
        $warnings = array_unique($warnings, SORT_REGULAR);

        return (array) $warnings;
    }
}
