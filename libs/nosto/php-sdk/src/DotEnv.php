<?php
/**
 * Copyright (c) 2015, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2015 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

/**
 * Helper class for loading local environment variables and assigning them to $_ENV.
 */
class NostoDotEnv
{
    /**
     * Initializes the environment variables from ".env" if it exists.
     *
     * @param string $path the path where to find the ".env" file.
     * @param string $fileName the name of the file; defaults to ".env".
     */
    public function init($path, $fileName = '.env')
    {
        $file = (!empty($path) ? rtrim($path, '/').'/' : '').$fileName;
        if (is_file($file) && is_readable($file)) {
            foreach ($this->parseFile($file) as $line) {
                $this->setEnvVariable($line);
            }
        }
    }

    /**
     * Parses the ".env" file into lines and returns them as an array.
     *
     * @param string $file the path of the file to parse.
     * @return array the parsed lines from the file.
     */
    protected function parseFile($file)
    {
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        ini_set('auto_detect_line_endings', $autodetect);
        return is_array($lines) ? $lines : array();
    }

    /**
     * Sets a non-existent variable in $_ENV.
     *
     * @param string $var the environment variable to set.
     */
    protected function setEnvVariable($var)
    {
        if (strpos(trim($var), '#') !== 0 && strpos($var, '=') !== false) {
            list($name, $value) = $this->normalizeEnvVariable($var);
            if (!isset($_ENV[$name])) {
                $_ENV[$name] = $value;
            }
        }
    }

    /**
     * Normalizes the given variable into a variable name and value.
     *
     * @param string $var the variable to normalize.
     * @return array the variable name and value as an array.
     */
    protected function normalizeEnvVariable($var)
    {
        list($name, $value) = array_map('trim', explode('=', $var, 2));
        $name = $this->sanitizeVariableName($name);
        $value = $this->sanitizeVariableValue($value);
        $value = $this->resolveNestedVariables($value);
        return array($name, $value);
    }

    /**
     * Sanitizes the variable value, i.e. strips quotes.
     *
     * @param string $value the variable value to sanitize.
     * @return string the sanitized value.
     */
    protected function sanitizeVariableValue($value)
    {
        $value = trim($value);
        if ($value) {
            if (strpbrk($value[0], '"\'') !== false) { // value starts with a quote
                $quote = $value[0];
                $regexPattern = sprintf(
                    '/^
                    %1$s          # match a quote at the start of the value
                    (             # capturing sub-pattern used
                     (?:          # we do not need to capture this
                      [^%1$s\\\\] # any character other than a quote or backslash
                      |\\\\\\\\   # or two backslashes together
                      |\\\\%1$s   # or an escaped quote e.g \"
                     )*           # as many characters that match the previous rules
                    )             # end of the capturing sub-pattern
                    %1$s          # and the closing quote
                    .*$           # and discard any string after the closing quote
                    /mx',
                    $quote
                );
                $value = preg_replace($regexPattern, '$1', $value);
                $value = str_replace("\\$quote", $quote, $value);
                $value = str_replace('\\\\', '\\', $value);
            } else {
                $parts = explode(' #', $value, 2);
                $value = $parts[0];
            }
            $value = trim($value);
        }
        return $value;
    }

    /**
     * Sanitizes the variable name, i.e. strips quotes.
     *
     * @param string $name the variable name to sanitize.
     * @return string the sanitized name.
     */
    protected function sanitizeVariableName($name)
    {
        return trim(str_replace(array('\'', '"'), '', $name));
    }

    /**
     * Look for {$name} patterns in the variable value and replace with an existing environment variable.
     *
     * @param string $value the variable value to search for nested variables.
     * @return string the resolved variable value.
     */
    protected function resolveNestedVariables($value)
    {
        if (strpos($value, '$') !== false) {
            $value = preg_replace_callback('/{\$([a-zA-Z0-9_]+)}/', array($this, 'getMatchedVariable'), $value);
        }
        return $value;
    }

    /**
     * Callback for getting the matched variable value.
     *
     * @param array $match the match from preg_replace_callback().
     * @return mixed the matched variable value or the variable name if not found.
     */
    protected function getMatchedVariable($match)
    {
        return isset($_ENV[$match[1]]) ? $_ENV[$match[1]] : $match[0];
    }
}
