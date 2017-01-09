<?php

/**
 * AWS Template Generator
 *
 * PHP version 7
 *
 * @category  N/A
 * @package   N/A
 * @author    Bill Hayden <hayden@haydentech.com>
 * @copyright 2017 Bill Hayden
 * @license   Public Domain
 * @link      https://github.com/haydentech/vanad2
 */

/**
 * AWS Template Generator class
 *
 * Creates and outputs an AWS template to either text or color-coded HTML
 *
 * @category  N/A
 * @package   N/A
 * @author    Bill Hayden <hayden@haydentech.com>
 * @copyright 2017 Bill Hayden
 * @license   Public Domain
 * @link      https://github.com/haydentech/vanad2
 */
class AWS_Template_Generator
{
    protected $instances = 1;
    protected $instance_type = 't2.micro';
    protected $allow_ssh_from = '0.0.0.0/0';

    protected $aws_php_template;
    protected $aws_json_template = '
{
  "AWSTemplateFormatVersion": "2010-09-09",
  "Outputs": {
    "PublicIP": {
      "Description": "Public IP address of the newly created EC2 instance",
      "Value": {
        "Fn::GetAtt": [
          "EC2Instance",
          "PublicIp"
        ]
      }
    }
  },
  "Resources": {
    "EC2Instance": {
      "Properties": {
        "ImageId": "ami-b97a12ce",
        "InstanceType": "t2.micro",
        "SecurityGroups": [
          {
            "Ref": "InstanceSecurityGroup"
          }
        ]
      },
      "Type": "AWS::EC2::Instance"
    },
    "InstanceSecurityGroup": {
      "Properties": {
        "GroupDescription": "Enable SSH access via port 22",
        "SecurityGroupIngress": [
          {
            "CidrIp": "0.0.0.0/0",
            "FromPort": "22",
            "IpProtocol": "tcp",
            "ToPort": "22"
          }
        ]
      },
      "Type": "AWS::EC2::SecurityGroup"
    }
  }
}
';

    /**
     * Set the number of EC2 instances to create in the template
     *
     * @param int $count number of EC2 instances
     *
     * @return void
     */
    public function setInstanceCount($count)
    {
        $this->instances = $count;
    }

    /**
     * Set the type of EC2 instances to create in the template
     *
     * @param string $type type (i.e. size) of the EC2 instance
     *
     * @return void
     */
    public function setInstanceType($type)
    {
        $this->instance_type = $type;
    }

    /**
     * Set the allowed IP range for SSH connections
     *
     * @param string $range IP range from which to allow SSH
     *
     * @return void
     */
    public function setSSHAllowedRange($range)
    {
        $this->allow_ssh_from = $range.'/32';
    }

    /**
     * Convert the stock AWS template to the form request by the caller
     *
     * @return void
     */
    public function getTransformedTemplate()
    {
        $transformedTemplate = json_decode($this->aws_json_template, true);
        $transformedTemplate['Resources']['EC2Instance']
            ['Properties']['InstanceType'] = $this->instance_type;
        $transformedTemplate['Resources']['InstanceSecurityGroup']
            ['Properties']['SecurityGroupIngress'][0]['CidrIp']
                = $this->allow_ssh_from;

        for ($i = 1; $i < $this->instances; $i++) {
            $key_name = 'EC2Instance'.($i + 1);
            $transformedTemplate['Resources'][$key_name]
                = $transformedTemplate['Resources']['EC2Instance'];
        }

        ksort($transformedTemplate['Resources']);

        return $transformedTemplate;
    }


    /**
     * Format and print output suitable for the command line
     *
     * @return string plain text output
     */
    public function textOutput()
    {
        $output = json_encode($this->getTransformedTemplate(), JSON_PRETTY_PRINT);
        // Match the exact requested output style
        $output = str_replace('    ', "\t", $output);
        $output = str_replace("\t", '  ', $output);
        $output = str_replace('\/', '/', $output);
        return $output;
    }


    /**
     * Format and print output suitable for a web browser
     *
     * @return string HTML formatted colorized text output
     */
    public function htmlOutput()
    {
        $output = '<html><head><style>';
        $output .= '.json-key { color: brown; } .json-val { color: navy; }';
        $output .= '</style></head><body><pre>';

        $encoded_json = $this->textOutput();
        $encoded_json = preg_replace(
            '/(\".*\"):/i',
            '<span class="json-key">${1}</span>:', $encoded_json
        );
        $encoded_json = preg_replace(
            '/:\s+(\".*\")/i',
            ': <span class="json-val">${1}</span>', $encoded_json
        );
        $encoded_json = preg_replace(
            '/(\s+\".*\")([,]?)/i',
            '<span class="json-val">${1}</span>${2}', $encoded_json
        );
        
        $output .= $encoded_json.'</pre></body></html>';
        return $output;
    }
}

?>
