<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/2
 * Time: 下午6:15
 */

namespace Yyg\Tasks;

use Aws\Exception\AwsException;

class Upload_imageTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $configs, $db;
        $img_id    = $task['argv']['img_id'];
        $img_path  = $task['argv']['img_path'];
        $img_type  = $task['argv']['img_type'];
        $save_path = $task['argv']['save_path'];

        $body = file_get_contents($img_path);


        $bucket                = $configs['services']['aws']['bucket'];
        $credentials['key']    = $configs['services']['aws']['access_key'];
        $credentials['secret'] = $configs['services']['aws']['secret_key'];

        $s3 = new \Aws\S3\S3Client(
            ['credentials' => $credentials, 'version' => 'latest', 'region' => 'ap-southeast-1']
        );

        try{
            $result = $s3->putObject(
                [
                    'Bucket'      => $bucket,
                    'Key'         => $save_path,
                    'Body'        => $body,
                    'Acl'         => 'public-read',
                    'ContentType' => $img_type,
                ]
            );

            if($result && $result['@metadata']['statusCode'] == 200) {
                $up_time = time();
                $save_path = "/" . $save_path;
                $sql = "update sp_image_list set img_path ='$save_path' , update_time = $up_time  where id = $img_id";
                $ret = $db->query($sql);
                if($ret) {
                    mdebug("update s3 path successfully");
                } else {
                    mdebug("update s3 path failed");
                }
            } else {
                mdebug("upload image to s3 failed : %d", $result['@@metadata']['statusCode']);
            }

        } catch(AwsException $e) {
            merror("s3 upload exception %s", $e->getAwsErrorMessage());
        }

    }
    
}