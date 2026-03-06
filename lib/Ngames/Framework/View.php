<?php

/*
 * Copyright (c) 2014-2021 NGames
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ngames\Framework;

use Ngames\Framework\Router\Route;

/**
 * This class represents a view.
 *
 * @property string content In case it's a parent view, there will be a 'content' property containing the child view content
 *
 */
class View // NOSONAR - view class with helpers, splitting would be overengineering
{
    /**
     * Define the default layout when not explicitely set.
     *
     * @var string
     */
    public const DEFAULT_LAYOUT = 'default';

    /**
     * Extension used for views.
     * Not changeable but could be.
     *
     * @var string
     */
    public const VIEWS_EXTENSION = '.phtml';

    /**
     * Variable name storing placeholders.
     * If overriden by client application, an exception is thrown.
     *
     * @var string
     */
    public const VARIABLE_PLACEHOLDERS = '__PLACEHOLDERS__';

    /**
     * Variable name storing stylesheets.
     *
     * @var string
     */
    public const VARIABLE_STYLESHEETS = '__STYLESHEETS__';

    /**
     * Variable name storing scripts.
     *
     * @var string
     */
    public const VARIABLE_SCRIPTS = '__SCRIPTS__';

    /**
     * View script template to render.
     *
     * @var string
     */
    protected $script = null;

    /**
     * Directory into which templates are fetched
     * Defaults to ROOT/src/View.
     *
     * @var string
     */
    protected $directory = null;

    /**
     * A view which will be rendered with the content of the current view in $content variable.
     *
     * @var View
     */
    protected $parentView = null;

    /**
     * Store all the variables set by the user.
     *
     * @var array
     */
    protected $variables = [];

    /**
     * Stores the current placeholder for the two-phase placeholder definition.
     *
     * @var string
     */
    protected $currentPlaceHolder = null;

    /**
     *
     * @param string|null $script
     *            The view script (under the view directory)
     */
    public function __construct($script = null)
    {
        $this->script = $script;
        $this->directory = ROOT_DIR . '/src/views/';
        $this->variables = [
            self::VARIABLE_PLACEHOLDERS => [],
            self::VARIABLE_STYLESHEETS => [],
            self::VARIABLE_SCRIPTS => []
        ];
    }

    /**
     * Magic setter to allow passing variables to the view.
     * Some keywords are reserved for internal usage.
     *
     * @param string $name
     * @param mixed $value
     * @throws \Ngames\Framework\Exception
     */
    public function __set($name, $value)
    {
        if ($name == self::VARIABLE_PLACEHOLDERS || $name == self::VARIABLE_SCRIPTS || $name == self::VARIABLE_STYLESHEETS) {
            throw new \Ngames\Framework\Exception('Cannot used reserved variable ' . $name);
        }

        // Add to the list of user variables
        $this->variables[$name] = $value;
    }

    /**
     * Magic getter to allow retrieving variables from the view.
     *
     * @param string $name
     * @return mixed $value
     * @throws \Ngames\Framework\Exception
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->variables)) {
            throw new \Ngames\Framework\Exception('Tried to access non existing variable ' . $name);
        }
        if ($name == self::VARIABLE_PLACEHOLDERS || $name == self::VARIABLE_SCRIPTS || $name == self::VARIABLE_STYLESHEETS) {
            throw new \Ngames\Framework\Exception('Tried to access reserved variable ' . $name);
        }

        return $this->variables[$name];
    }

    public function __unset($name)
    {
        unset($this->variables[$name]);
    }

    /**
     * Return the URL to the script that will be used to render the view (.phtml)
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets the script to render
     *
     * @param string $script
     * @return \Ngames\Framework\View
     */
    public function setScript($script)
    {
        $this->script = $script;

        return $this;
    }

    /**
     * Sets the script from a Route instance.
     *
     * @param Route $route
     * @return \Ngames\Framework\View
     */
    public function setScriptFromRoute(Route $route)
    {
        $moduleName = $route->getModuleName();
        $controllerName = $route->getControllerName();
        $actionName = $route->getActionName();

        return $this->setScript($moduleName . '/' . $controllerName . '/' . $actionName);
    }

    /**
     * Return the directory where view scripts are stored.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Sets the directory where view scripts are stored.
     *
     * @param string $directory
     * @return \Ngames\Framework\View
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Return parent view if any (layout for instance).
     *
     * @return \Ngames\Framework\View|null
     */
    public function getParentView()
    {
        return $this->parentView;
    }

    /**
     * Defines a parent view.
     * Content of the current view will be added as a variable of the parent view before rendering it ($content).
     *
     * @param \Ngames\Framework\View|null $parentView
     *            The path to the parent view. If null, parent view is disabled
     * @return \Ngames\Framework\View
     */
    public function setParentView($parentView)
    {
        $this->parentView = $parentView;

        return $this;
    }

    /**
     * This function is used for both set/get placeholder.
     * Switch relies on $value being null or not.
     * In getter mode, non-existing values are returned as empty string.
     *
     * @param string $name
     * @param string|null $value
     *            Default null, meaning get value
     * @return string
     */
    public function placeholder($name, $value = null)
    {
        if ($value !== null) {
            $this->variables[self::VARIABLE_PLACEHOLDERS][$name] = $value;
        } else {
            if (array_key_exists($name, $this->variables[self::VARIABLE_PLACEHOLDERS])) {
                return $this->variables[self::VARIABLE_PLACEHOLDERS][$name];
            } else {
                return '';
            }
        }
    }

    /**
     * Starts a placeholder.
     * Always use stopPlaceHolder() after or an exception will be thrown at view rendering time.
     *
     * @param string $name
     */
    public function startPlaceHolder($name)
    {
        if ($this->currentPlaceHolder != null) {
            throw new \Ngames\Framework\Exception('Cannot start a new placeholder: previous not stopped');
        }

        $this->currentPlaceHolder = $name;
        ob_start();
    }

    /**
     * End the placeholder.
     * Content is returned so that it can be rendered in place if needed.
     */
    public function stopPlaceHolder()
    {
        if ($this->currentPlaceHolder == null) {
            throw new \Ngames\Framework\Exception('Cannot stop a placeholder: none started');
        }

        $placeHolderContent = ob_get_contents();
        ob_end_clean();

        $this->variables[self::VARIABLE_PLACEHOLDERS][$this->currentPlaceHolder] = $placeHolderContent;
        $this->currentPlaceHolder = null;

        return $placeHolderContent;
    }

    /**
     * Prepend a stylesheet: add it to the list, at the begining.
     *
     * @param string $path
     */
    public function prependStylesheet($path)
    {
        if (in_array($path, $this->variables[self::VARIABLE_STYLESHEETS])) {
            unset($this->variables[self::VARIABLE_STYLESHEETS][array_search($path, $this->variables[self::VARIABLE_STYLESHEETS])]);
        }

        array_unshift($this->variables[self::VARIABLE_STYLESHEETS], $path);
    }

    /**
     * Append a stylesheet: add it to the list, at the end.
     *
     * @param string $path
     */
    public function appendStylesheet($path)
    {
        if (in_array($path, $this->variables[self::VARIABLE_STYLESHEETS])) {
            unset($this->variables[self::VARIABLE_STYLESHEETS][array_search($path, $this->variables[self::VARIABLE_STYLESHEETS])]);
        }

        array_push($this->variables[self::VARIABLE_STYLESHEETS], $path);
    }

    /**
     * Return a string containing the HTML to include the stylesheets.
     */
    public function renderStylesheets()
    {
        $result = '';

        foreach ($this->variables[self::VARIABLE_STYLESHEETS] as $stylesheet) {
            $result .= '<link rel="stylesheet" href="' . $stylesheet . '" />';
        }

        return $result;
    }

    /**
     * Prepend a script: add it to the list, at the begining.
     *
     * @param string $path
     */
    public function prependScript($path)
    {
        if (in_array($path, $this->variables[self::VARIABLE_SCRIPTS])) {
            unset($this->variables[self::VARIABLE_SCRIPTS][array_search($path, $this->variables[self::VARIABLE_SCRIPTS])]);
        }

        array_unshift($this->variables[self::VARIABLE_SCRIPTS], $path);
    }

    /**
     * Append a script: add it to the list, at the end.
     *
     * @param string $path
     */
    public function appendScript($path)
    {
        if (in_array($path, $this->variables[self::VARIABLE_SCRIPTS])) {
            unset($this->variables[self::VARIABLE_SCRIPTS][array_search($path, $this->variables[self::VARIABLE_SCRIPTS])]);
        }

        array_push($this->variables[self::VARIABLE_SCRIPTS], $path);
    }

    /**
     * Return a string containing the HTML to include the scripts.
     */
    public function renderScripts()
    {
        $result = '';

        foreach ($this->variables[self::VARIABLE_SCRIPTS] as $script) {
            $result .= '<script src="' . $script . '"></script>';
        }

        return $result;
    }

    /**
     * Helper function to set layout (actually parent view), from a string.
     * It also changes the script directory of the parent view.
     *
     * @param string|null $layout
     *
     * @return \Ngames\Framework\View
     */
    public function setLayout($layout)
    {
        if ($layout !== null) {
            $parentViewDirectory = $this->directory . 'layouts/';
            $parentView = new self($layout);
            $parentView->setDirectory($parentViewDirectory);
            $this->setParentView($parentView);
        } else {
            $this->disableLayout();
        }

        return $this;
    }

    /**
     * Disable the layout, aka remove the parent view.
     */
    public function disableLayout()
    {
        $this->setParentView(null);
    }

    /**
     * Renders the view.
     *
     * @param string|null $script
     * @throws Exception
     * @throws \Ngames\Framework\Exception
     * @throws \Exception
     * @return string
     */
    public function render($script = null)
    {
        // Override the script?
        if ($script !== null) {
            $this->setScript($script);
        }

        // Check the path to rendered file
        $moduleFullPath = $this->directory . $this->getScript();
        $scriptFullPath = $moduleFullPath . self::VIEWS_EXTENSION;
        if (!is_readable($scriptFullPath)) {
            $scriptFullPath = $moduleFullPath . '/index' . self::VIEWS_EXTENSION;

            if (!is_readable($scriptFullPath)) {
                throw new Exception($moduleFullPath . ' not found');
            }
        }

        // Put the variables in scope
        foreach ($this->variables as $variableName => $variableValue) {
            $$variableName = $variableValue; //NOSONAR
        }

        // Render
        ob_start();
        try {
            include $scriptFullPath; // NOSONAR - view templates are intentionally included multiple times

            // Check that after script rendering, a placeholder was not being defined
            if ($this->currentPlaceHolder != null) {
                throw new \Ngames\Framework\Exception('Placeholder not stopped at the end of view rendering');
            }
        } catch (\Throwable $e) {
            ob_end_clean();
            throw new \Ngames\Framework\Exception('Exception caught during view rendering', 0, $e);
        }
        $renderContent = ob_get_contents();
        ob_end_clean();

        // Call parent if needed
        if ($this->parentView !== null) {
            // Save parent variables
            $parentVariables = $this->parentView->variables;

            // Add child view variables to parent
            $this->parentView->variables = array_merge_recursive($this->variables, $parentVariables);

            // Set parent view 'content' variable
            $this->parentView->content = $renderContent;

            // Render parent
            $renderContent = $this->parentView->render();

            // Restore parent user variables
            $this->parentView->variables = $parentVariables;
        }

        return $renderContent;
    }
}
