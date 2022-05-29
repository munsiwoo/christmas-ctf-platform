<?php
error_reporting(0);

class MunTemplate {
    private $template_path;

    function __construct($template_path){
        if(substr($template_path, -1) == '/')
            $this->template_path = substr($template_path, 0, -1);
        else
            $this->template_path = $template_path;
    }

    private function process_import($html){
        $parse_regexp = "/\@import(?:\s+)?(?<mun>\(((?:[^()]|(?&mun))*)\))/mis";
        preg_match_all($parse_regexp, $html, $matches);

        foreach($matches[2] as $filename){
            $import_file = $this->template_path.'/'.trim($filename);
            if(!file_exists($import_file) && $this->debug){
                throw new Exception("Muntemplate Error: file doesn't exist. ({$import_file})");
                exit;
            }
            $filename = str_replace('.', '[.]', $filename);
            $replace_regexp = "/\@import(?:\s+)?(?<mun>\(((?:[^()]|(?&mun))*){$filename}\s*\))/mis";
            $file_contents = file_get_contents($import_file);
            $html = preg_replace($replace_regexp, $file_contents, $html);
        }
        return $html;
    }
    private function process_var($html){
        $parse_regexp = "/\@var(?:\s+)?(?<mun>\(((?:[^()]|(?&mun))*)\))/mis";
        $replace_code = '<?php echo $2; ?>';
        $retval = preg_replace($parse_regexp, $replace_code, $html);

        return $retval;
    }
    private function process_for($html){
        $start_regexp = "/\@mun\s+for(?:\s+)?(?<mun>\(((?:[^()]|(?&mun))*)\))/mis";
        $end_regexp = "/\@endfor(?:\s+)?$/mis";

        $html = preg_replace($start_regexp, '<?php for($2) : ?>', $html);
        $retval = preg_replace($end_regexp, '<?php endfor; ?>', $html);

        return $retval;
    }
    private function process_foreach($html){
        $start_regexp = "/\@mun\s+foreach(?:\s+)?(?<mun>\(((?:[^()]|(?&mun))*)\))/mis";
        $end_regexp = "/\@endforeach(?:\s+)?$/mis";

        $html = preg_replace($start_regexp, '<?php foreach($2) : ?>', $html);
        $retval = preg_replace($end_regexp, '<?php endforeach; ?>', $html);

        return $retval;
    }
    private function process_if($html){
        $if_regexp = "/\@mun\s+if(?:\s+)?(?<mun>\(((?:[^()]|(?&mun))*)\))/mis";
        $elif_regexp = "/\@mun\s+elif(?:\s+)?(?<mun>\(((?:[^()]|(?&mun))*)\))/mis";
        $else_regexp = "/\@mun\s+else/mis";
        $end_regexp = "/\@endif/mis";

        $html = preg_replace($if_regexp, '<?php if($2) : ?>', $html);
        $html = preg_replace($elif_regexp, '<?php elseif($2) : ?>', $html);
        $html = preg_replace($else_regexp, '<?php else : ?>', $html);
        $retval = preg_replace($end_regexp, '<?php endif; ?>', $html);

        return $retval;
    }
    private function remove_php_tag($html){
        $retval = preg_replace('/(<\?(?!xml))/', '&lt;?', $html);
        return $retval;
    }
    function render_template($template, $vars=[], $debug_mode=false, $php_tag=false){
        $error_report  = '<?php error_reporting(';
        $error_report .= $debug_mode ? 'E_ALL); ?>' : '0); ?>';
        $this->debug = $debug_mode;

        foreach($vars as $var_name=>$value){
            ${$var_name} = $value; // ${variable_name} = value;
        }

        $exec_code = file_get_contents($this->template_path.'/'.$template);
        if(!$php_tag){
            $exec_code = $this->remove_php_tag($exec_code);
        }

        $exec_code = $this->process_import($exec_code);
        $exec_code = $this->process_for($exec_code);
        $exec_code = $this->process_foreach($exec_code);
        $exec_code = $this->process_if($exec_code);
        $exec_code = $this->process_var($exec_code);
        $exec_code = $error_report.$exec_code;
        eval("?>$exec_code");
        return true;
    }
    function render_template_string($exec_code, $vars=[], $debug_mode=false, $php_tag=false){
        $error_report  = '<?php error_reporting(';
        $error_report .= $debug_mode ? 'E_ALL); ?>' : '0); ?>';

        foreach($vars as $var_name=>$value){
            ${$var_name} = $value; // ${variable_name} = value;
        }

        if(!$php_tag){
            $exec_code = $this->remove_php_tag($exec_code);
        }

        $exec_code = $this->process_for($exec_code);
        $exec_code = $this->process_foreach($exec_code);
        $exec_code = $this->process_if($exec_code);
        $exec_code = $this->process_var($exec_code);
        $exec_code = $error_report.$exec_code;
        
        eval("?>$exec_code");
        return true;
    }
}
