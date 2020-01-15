<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ParameterExtension extends AbstractExtension{
	protected $params;

	public function __construct(ParameterBagInterface $params){
		$this->params = $params;
	}

	public function getParameter($param){
		return $this->params->has($param) ? $this->params->get($param) : '';
	}

	public function getFunctions(): array{
		return [
			new TwigFunction('get_parameter', [$this, 'getParameter'])
		];
	}
}