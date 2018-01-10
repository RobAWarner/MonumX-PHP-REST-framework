<?php
namespace MonumX\Cli;

class Request {
    private $_cmdParams = array();
    private $_cliArgs = array();

    public function __construct(array $cmdArgs, array $cmdParams = array()) {
        $this->_cmdParams = $cmdParams;
        $this->_cliArgs = $cmdArgs;
    }

    // Command params
    public function cmdParam(string $key) {
        $key = strtolower($key);
        return $this->_cmdParams[$key] ?? false;
    }

    // Full command line arguments
    public function cmdArgs() {
        return $this->_cliArgs;
    }
}
?>
