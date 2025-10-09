<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Scheduler;

use InvalidArgumentException;
use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * A class that represents execution argument of a task.
 *
 * @author Ibrahim
 *
 * @version 1.0
 *
 * @since 2.3.1
 */
class TaskArgument implements JsonI {
    /**
     *
     * @var string
     *
     * @since 1.0
     */
    private $argName;
    private $argVal;
    private $default;
    /**
     *
     * @var string
     *
     * @since 1.0
     */
    private $description;
    /**
     * Creates new instance of the class.
     *
     * @param string $name The name of the argument. It will be considered invalid
     * if it contains one of the following characters: '=', '&', '#' and '?'.
     *
     * @param string $desc A string that describes how the argument will affect
     * task execution. It must be non-empty string in order to be set.
     *
     * @since 1.0
     */
    public function __construct(string $name, string $desc = '') {
        $this->setName($name);

        if (!$this->setDescription($desc)) {
            $this->setDescription('NO DESCRIPTION');
        }
    }
    /**
     * Returns the default value of the argument.
     *
     * The default value is usually used if the argument has no value
     * provided.
     *
     * @return string|null If default value is set, it's returned as string.
     * Other than that, null is returned.
     */
    public function getDefault() {
        return $this->default;
    }
    /**
     * Returns argument description.
     *
     * @return string A string that describes how the argument will affect task
     * execution. Default return value is 'NO DESCRIPTION'.
     *
     * @since 1.0
     */
    public function getDescription() : string {
        return $this->description;
    }
    /**
     * Returns the name of the argument.
     *
     * @return string The name of the argument.
     *
     * @since 1.0
     */
    public function getName() : string {
        return $this->argName;
    }
    /**
     * Returns the value of task argument.
     *
     *
     * @return string|null If the value of the argument is set, it will be returned
     * as string. Other than that, null is returned.
     *
     * @since 1.0
     */
    public function getValue() {
        return $this->argVal;
    }
    /**
     * Sets a default value for the argument to use in case it was not
     * provided.
     *
     * @param string $default A string that represents the default value
     * of the argument.
     */
    public function setDefault(string $default) {
        if (strlen($default) == 0) {
            return;
        }
        $this->default = $default;
    }
    /**
     * Sets a description for the argument.
     *
     * @param string $desc A string that describes how the argument will affect
     * task execution. It must be non-empty string in order to be set.
     *
     * @return boolean If the description is set, the method will return true.
     * false if not set.
     *
     * @since 1.0
     */
    public function setDescription(string $desc): bool {
        $trimmed = trim($desc);

        if (strlen($trimmed) > 0) {
            $this->description = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Sets the name of the argument.
     *
     * @param string $name The name of the argument. It will be considered invalid
     * if it contains one of the following characters: '=', '&', '#' and '?'.
     *
     * @throws InvalidArgumentException If the name of the argument is invalid.
     */
    public function setName(string $name) {
        $nTrim = trim($name);

        if (!AbstractTask::isNameValid($nTrim)) {
            if (strlen($nTrim) == 0) {
                throw new InvalidArgumentException('Invalid argument name: <empty string>');
            } else {
                throw new InvalidArgumentException('Invalid argument name: '.$nTrim);
            }
        }
        $this->argName = $nTrim;
    }
    /**
     * Sets the value of the argument.
     *
     * @param string $val A string that represents the value of the argument.
     */
    public function setValue(string $val) {
        $this->argVal = $val;
    }
    /**
     * Returns an object that represents the argument in JSON.
     *
     * @return Json An object that holds the following JSON attributes:
     * <ul>
     * <li>name</li>
     * <li>description</li>
     * </ul>
     *
     * @since 1.0
     */
    public function toJSON() : Json {
        $json = new Json([
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'default' => $this->getDefault()
        ]);
        $json->setPropsStyle('snake');

        return $json;
    }
}
