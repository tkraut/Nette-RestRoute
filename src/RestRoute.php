<?php

namespace AdamStipak;

use Nette\Application\IRouter;
use Nette\NotImplementedException;
use Nette\InvalidStateException;
use Nette\Http\Request as HttpRequest;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Http\Url;

/**
 * @autor Adam Štipák <adam.stipak@gmail.com>
 */
class RestRoute implements IRouter {

  /** @var string */
  protected $module;

  /** @var array */
  protected $formats = array('json');

  public function __construct($module, array $formats) {
    $this->module = $module;
    $this->formats = $formats;
  }

  /**
   * Maps HTTP request to a Request object.
   * @param \Nette\Http\IRequest $httpRequest
   * @return Request|NULL
   */
  public function match(IRequest $httpRequest) {
    $cleanPath = str_replace($httpRequest->getUrl()->getBasePath(), '', $httpRequest->getUrl()->getPath());

    $params = array();
    list($path, $params['format']) = explode('.', $cleanPath);
    $this->checkFormat($params['format']);
    $params['action'] = $this->detectAction($httpRequest);
    $frags = explode('/', $path);

    // Identificator.
    if (count($frags) % 2 === 0) {
      $params['id'] = array_pop($frags);
    }
    $presenterName = ucfirst(array_pop($frags));

    // Associations.
    $assoc = array();
    if (count($frags) > 0 && count($frags) % 2 === 0) {
      foreach ($frags as $k => $f) {
        if ($k % 2 !== 0) continue;

        $assoc[$f] = $frags[$k + 1];
      }
    }

    $params['associations'] = $assoc;
    $params['data'] = $this->readInput();
    $params['query'] = $httpRequest->getQuery();

    $req = new Request(
      $this->module . ':' . $presenterName,
      $httpRequest->getMethod(),
      $params
    );

    return $req;
  }

  protected function detectAction(HttpRequest $request) {
    $method = $request->getMethod();

    switch ($method) {
      case 'GET':
        $action = 'read';
        break;
      case 'POST':
        $action = 'create';
        break;
      case 'PUT':
        $action = 'update';
        break;
      case 'DELETE':
        $action = 'delete';
        break;
      default:
        throw new InvalidStateException('Method ' . $method . ' is not allowed.');
    }

    return $action;
  }

  /**
   * @param $path
   * @throws \Nette\NotImplementedException
   * @return string
   */
  protected function checkFormat($path) {
    $frags = explode('.', $path);
    $format = end($frags);

    if (!in_array($format, $this->formats)) {
      throw new NotImplementedException("Format {$format} is not supported.");
    }
    return $format;
  }

  /**
   * @return array|null
   */
  protected function readInput() {
    return file_get_contents('php://input');
  }

  /**
   * Constructs absolute URL from Request object.
   * @param Request $appRequest
   * @param \Nette\Http\Url $refUrl
   * @throws \Nette\NotImplementedException
   * @return string|NULL
   */
  public function constructUrl(Request $appRequest, Url $refUrl) {
    $url = '';
    $params = $appRequest->getParameters();

    foreach ($params['associations'] as $k => $v) {
      $url .= $k . '/' . $v;
    }

    $resource = explode(':', $appRequest->getPresenterName());
    $resource = end($resource);
    $resource = strtolower($resource);
    $url .= (count($params['associations']) ? '/' : '') . $resource;

    if (!empty($params['id'])) {
      $url .= '/' . $params['id'];
    }

    $url .= '.' . $params['format'];

    if (count($params['query'])) {
      $url .= '?' . http_build_query($params['query']);
    }

    $base = $refUrl->baseUrl;
    return $base . $url;
  }
}