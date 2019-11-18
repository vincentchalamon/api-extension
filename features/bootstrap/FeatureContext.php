<?php

/*
 * This file is part of the API Extension project.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);
/*
 * This file is originally part of the Behat Symfony2 extension project
 * (https://github.com/Behat/Symfony2Extension).
 *
 * (c) 2012 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

use Behat\Behat\Context\Context as ContextInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Behatch\Json\JsonSchema;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Christophe Coevoet
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class FeatureContext implements ContextInterface
{
    /**
     * @var string
     */
    private $phpBin;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var JsonInspector
     */
    private $inspector;

    /**
     * Prepares test folders in the temporary directory.
     *
     * @BeforeScenario
     */
    public function prepareProcess()
    {
        $phpFinder = new PhpExecutableFinder();
        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }
        $this->phpBin = $php;
        $this->process = null;
    }

    /**
     * Runs behat command with provided parameters.
     *
     * @When /^I run "behat(?: ((?:\"|[^"])*))?"$/
     *
     * @param string $argumentsString
     */
    public function iRunBehat($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, ['\'' => '"']);

        $this->process = new Process([
            $this->phpBin,
            BEHAT_BIN_PATH,
            $argumentsString,
            '--lang=en',
            '--no-colors',
            '--format-settings=\'{"timer": false}\'',
        ], __DIR__.'/../../tests/app');
        $this->process->run();
    }

    /**
     * Checks whether previously runned command passes|failes with provided output.
     *
     * @Then /^it should (fail|pass) with:$/
     *
     * @param string       $success "fail" or "pass"
     * @param PyStringNode $text    PyString text instance
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        $this->itShouldFail($success);
        $this->theOutputShouldContain($text);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @Then the output should contain:
     *
     * @param PyStringNode $text PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        Assert::assertContains($this->getExpectedOutput($text), $this->getOutput());
    }

    /**
     * @Then the JSON output should be equal to:
     */
    public function theJsonOutputShouldBeEqualTo(PyStringNode $text)
    {
        $output = str_replace('│', '', $this->getOutput());
        $output = substr($output, strpos($output, '{'));
        $output = substr($output, 0, strrpos($output, '}') + 1);
        $output = json_decode($output, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \LogicException('Unable to detect a valid JSON response.');
        }
        $json = json_decode($text->getRaw(), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \LogicException('Invalid JSON.');
        }
        $diff = array_map('unserialize', array_diff(array_map('serialize', $output), array_map('serialize', $json)));
        if (0 < count($diff)) {
            throw new \LogicException("JSON response does not match:\n".print_r($diff, true));
        }
    }

    /**
     * @Then the JSON output should be equal to this schema:
     */
    public function theJsonOutputShouldBeEqualToThisSchema(PyStringNode $schema)
    {
        $output = str_replace('│', '', $this->getOutput());
        $output = substr($output, strpos($output, '{'));
        $output = substr($output, 0, strrpos($output, '}') + 1);
        if (null === $this->inspector) {
            $this->inspector = new JsonInspector('javascript');
        }
        $this->inspector->validate(new Json($output), new JsonSchema($schema->getRaw()));
    }

    /**
     * @param PyStringNode $expectedText
     *
     * @return string
     */
    private function getExpectedOutput(PyStringNode $expectedText)
    {
        $text = strtr($expectedText->getRaw(), ['\'\'\'' => '"""']);

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback(
                '/ features\/[^\n ]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );
            $text = preg_replace_callback(
                '/\<span class\="path"\>features\/[^\<]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );
            $text = preg_replace_callback(
                '/\+[fd] [^ ]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );
        }

        return $text;
    }

    /**
     * Checks whether previously runned command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
     *
     * @param string $success "fail" or "pass"
     */
    public function itShouldFail($success)
    {
        if ('fail' === $success) {
            if (0 === $this->getExitCode()) {
                echo 'Actual output:'.PHP_EOL.PHP_EOL.$this->getOutput();
            }

            Assert::assertNotEquals(0, $this->getExitCode());
        } else {
            if (0 !== $this->getExitCode()) {
                echo 'Actual output:'.PHP_EOL.PHP_EOL.$this->getOutput();
            }

            Assert::assertEquals(0, $this->getExitCode());
        }
    }

    private function getExitCode()
    {
        return $this->process->getExitCode();
    }

    private function getOutput()
    {
        $output = $this->process->getErrorOutput().$this->process->getOutput();

        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        return trim(preg_replace('/ +$/m', '', $output));
    }
}
