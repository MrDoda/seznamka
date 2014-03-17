<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Latte\Macros
 */



/**
 * Base IMacro implementation. Allows add multiple macros.
 *
 * @author     David Grudl
 * @package Nette\Latte\Macros
 */
class NMacroSet extends NObject implements IMacro
{
	/** @var NLatteCompiler */
	private $compiler;

	/** @var array */
	private $macros;


	public function __construct(NLatteCompiler $compiler)
	{
		$this->compiler = $compiler;
	}


	public function addMacro($name, $begin, $end = NULL, $attr = NULL)
	{
		$this->macros[$name] = array($begin, $end, $attr);
		$this->compiler->addMacro($name, $this);
		return $this;
	}


	public static function install(NLatteCompiler $compiler)
	{
		return new self($compiler);
	}


	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
	}


	/**
	 * New node is found.
	 * @return bool
	 */
	public function nodeOpened(NMacroNode $node)
	{
		if ($this->macros[$node->name][2] && $node->htmlNode) {
			$node->isEmpty = TRUE;
			$this->compiler->setContext(NLatteCompiler::CONTEXT_DOUBLE_QUOTED);
			$res = $this->compile($node, $this->macros[$node->name][2]);
			$this->compiler->setContext(NULL);
			if (!$node->attrCode) {
				$node->attrCode = "<?php $res ?>";
			}
		} else {
			$node->isEmpty = !isset($this->macros[$node->name][1]);
			$res = $this->compile($node, $this->macros[$node->name][0]);
			if (!$node->openingCode) {
				$node->openingCode = "<?php $res ?>";
			}
		}
		return $res !== FALSE;
	}


	/**
	 * Node is closed.
	 * @return void
	 */
	public function nodeClosed(NMacroNode $node)
	{
		$res = $this->compile($node, $this->macros[$node->name][1]);
		if (!$node->closingCode) {
			$node->closingCode = "<?php $res ?>";
		}
	}


	/**
	 * Generates code.
	 * @return string
	 */
	private function compile(NMacroNode $node, $def)
	{
		$node->tokenizer->reset();
		$writer = NPhpWriter::using($node, $this->compiler);
		if (is_string($def)&& substr($def, 0, 1) !== "\0") {
			return $writer->write($def);
		} else {
			return NCallback::create($def)->invoke($node, $writer);
		}
	}


	/**
	 * @return NLatteCompiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

}
