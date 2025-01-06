<?php

namespace Sajjadhossainshohagbd\Extractor\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;

class ExtractCommand extends Command
{
    protected $signature = 'extractor:run';
    protected $description = 'Extract validation rules from controllers and store them in a request file';

    public function handle()
    {
        // Get scan path
        $scanPath = config('extractor.scan_path');

        $finder = new Finder();
        $finder->files()->in($scanPath)->name('*.php');
        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        $rulesCollection = [];

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();

            try {
                $code = file_get_contents($filePath);
                $ast = $parser->parse($code);

                $traverser = new NodeTraverser();
                $visitor = new class extends NodeVisitorAbstract {
                    public $rules = [];

                    public function enterNode(Node $node)
                    {
                        // Detecting Validator::make() rules
                        if (
                            $node instanceof Node\Expr\StaticCall &&
                            $node->class instanceof Node\Name &&
                            $node->class->toString() === 'Validator' &&
                            $node->name->toString() === 'make'
                        ) {
                            // Extract the second argument (validation rules)
                            if (isset($node->args[1])) {
                                $rulesNode = $node->args[1]->value;

                                // Convert the AST node of the array to PHP code
                                if ($rulesNode instanceof Node\Expr\Array_) {
                                    $this->rules[] = $this->convertArrayNodeToPHP($rulesNode);
                                }
                            }
                        }

                        // Detecting $request->validate rules
                        if (
                            $node instanceof MethodCall &&
                            $node->var instanceof Variable &&
                            $node->var->name === 'request' &&
                            $node->name->toString() === 'validate'
                        ) {
                            // Extract the first argument (validation rules array)
                            if (isset($node->args[0])) {
                                $rulesNode = $node->args[0]->value;

                                // Convert the AST node of the array to PHP code
                                if ($rulesNode instanceof Node\Expr\Array_) {
                                    $this->rules[] = $this->convertArrayNodeToPHP($rulesNode);
                                }
                            }
                        }
                    }

                    private function convertArrayNodeToPHP(Node\Expr\Array_ $arrayNode)
                    {
                        $rules = [];
                        foreach ($arrayNode->items as $item) {
                            if ($item->key && $item->value) {
                                $key = $item->key instanceof Node\Scalar\String_ ? $item->key->value : null;
                                $value = $item->value instanceof Node\Scalar\String_ ? $item->value->value : collect(data_get($item->value, 'items'))->pluck('value.value')->values()->toArray();

                                if ($key) {
                                    $rules[$key] = $value;
                                }
                            }
                        }
                        return $rules;
                    }
                };

                $traverser->addVisitor($visitor);
                $traverser->traverse($ast);

                if (!empty($visitor->rules)) {
                    $rulesCollection[$filePath] = $visitor->rules;
                }

                dd($rulesCollection);
            } catch (Error $e) {
                throw $e;

                $this->error("Error parsing file $filePath: " . $e->getMessage());
            }
        }

        $this->displayRules($rulesCollection);
    }

    /**
     * Display the rules in a readable format.
     */
    protected function displayRules(array $rulesCollection)
    {
        foreach ($rulesCollection as $filePath => $rulesList) {
            $this->info("Validation rules found in $filePath:");
            foreach ($rulesList as $rules) {
                foreach ($rules as $field => $rule) {
                    $this->line("  $field: $rule");
                }
            }
            $this->line('');
        }
    }
}
