<?php

   
use PHPUnit\Framework\TestCase;
require_once dirname(__FILE__) . '/../AWS_Template.php';

class AWS_TemplateTest extends \PHPUnit_Framework_TestCase
{
        // A test for default output with no parameters
        public function testOutput1()
        {
	    $aws_template_class = new AWS_Template_Generator();
            $expected = '{
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
}';

            $actual = $aws_template_class->textOutput();
            $this->assertEquals($expected, $actual);
        }

        // A test for output with the following parameters:
	// --instances 2 --instance-type t2.small --allow-ssh-from 172.16.8.30
        public function testOutput2()
        {
	    $aws_template_class = new AWS_Template_Generator();
	    $aws_template_class->setInstanceCount(2);
	    $aws_template_class->setInstanceType('t2.small');
	    $aws_template_class->setSSHAllowedRange('172.16.8.30');
            $expected = '{
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
        "InstanceType": "t2.small",
        "SecurityGroups": [
          {
            "Ref": "InstanceSecurityGroup"
          }
        ]
      },
      "Type": "AWS::EC2::Instance"
    },
    "EC2Instance2": {
      "Properties": {
        "ImageId": "ami-b97a12ce",
        "InstanceType": "t2.small",
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
            "CidrIp": "172.16.8.30/32",
            "FromPort": "22",
            "IpProtocol": "tcp",
            "ToPort": "22"
          }
        ]
      },
      "Type": "AWS::EC2::SecurityGroup"
    }
  }
}';

            $actual = $aws_template_class->textOutput();
            $this->assertEquals($expected, $actual);
        }

    }

?>
