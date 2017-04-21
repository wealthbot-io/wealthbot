<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

interface ModelInterface {

    public function getId();

    public function setId($id);

    public function loadFromArray(array $data);
}