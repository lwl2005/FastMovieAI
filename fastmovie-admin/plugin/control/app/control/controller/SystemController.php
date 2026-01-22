<?php

namespace plugin\control\app\control\controller;

use app\Basic;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\enum\Filesystem;
use app\expose\enum\SubmitEvent;
use app\expose\trait\Config;
use app\model\Config as ModelConfig;
use support\Request;

class SystemController extends Basic
{
    use Config;
    public function basic(Request $request)
    {
        $this->channels_uid = $request->channels_uid;
        return $this->builder();
    }
    public function yidevs(Request $request)
    {
        $this->channels_uid = $request->channels_uid;
        return $this->builder();
    }
    public function payment(Request $request)
    {
        $this->channels_uid = $request->channels_uid;
        return $this->builder();
    }
    public function sms(Request $request)
    {
        $this->channels_uid = $request->channels_uid;
        return $this->tabsBuilder();
    }
    public function upload(Request $request)
    {
        $request = request();
        if ($request->method() === 'POST') {
            $D = $request->post();
            $config = [];
            $config['default'] = $D['default'];
            $config['max_size'] = (int)$D['max_size'];
            foreach ($D['ftp'] as $key => $value) {
                $config['storage']['ftp'][$key] = $value;
            }
            foreach ($D['s3'] as $key => $value) {
                if (in_array($key, ['key', 'secret'])) {
                    $config['storage']['s3']['credentials'][$key] = $value;
                } else {
                    $config['storage']['s3'][$key] = $value;
                }
            }
            foreach ($D['minio'] as $key => $value) {
                if (in_array($key, ['key', 'secret'])) {
                    $config['storage']['minio']['credentials'][$key] = $value;
                } else {
                    $config['storage']['minio'][$key] = $value;
                }
            }
            foreach ($D['oss'] as $key => $value) {
                $config['storage']['oss'][$key] = $value;
            }
            foreach ($D['qiniu'] as $key => $value) {
                $config['storage']['qiniu'][$key] = $value;
            }
            foreach ($D['cos'] as $key => $value) {
                $config['storage']['cos'][$key] = $value;
            }
            $ModelConfig = ModelConfig::where(['group' => 'filesystem', 'channels_uid' => $request->channels_uid])->find();
            if (!$ModelConfig) {
                $ModelConfig = new ModelConfig;
                $ModelConfig->group = 'filesystem';
                $ModelConfig->channels_uid = $request->channels_uid;
            }
            $ModelConfig->value = $config;
            $ModelConfig->save();
            return $this->success('保存成功');
        }
        $config = [
            'enable' => true,
            'default' => Filesystem::OSS['value'],
            'max_size' => 1024 * 1024 * 10, //单个文件大小10M
            'ext_yes' => [], //允许上传文件类型 为空则为允许所有
            'ext_no' => [], // 不允许上传文件类型 为空则不限制
            'storage' => [
                'ftp' => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\FtpAdapterFactory::class,
                    'host' => 'ftp.example.com',
                    'username' => 'username',
                    'password' => 'password',
                    'url' => '', // 静态文件访问域名
                    'port' => 21,
                    'root' => '/path/to/root',
                    'passive' => true,
                    'ssl' => true,
                    'timeout' => 30,
                    'ignorePassiveAddress' => false,
                    'timestampsOnUnixListingsEnabled' => true,
                ],
                'memory' => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\MemoryAdapterFactory::class,
                ],
                's3' => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\S3AdapterFactory::class,
                    'credentials' => [
                        'key' => 'S3_KEY',
                        'secret' => 'S3_SECRET',
                    ],
                    'region' => 'S3_REGION',
                    'version' => 'latest',
                    'bucket_endpoint' => false,
                    'use_path_style_endpoint' => false,
                    'endpoint' => 'S3_ENDPOINT',
                    'bucket_name' => 'S3_BUCKET',
                    'url' => '' // 静态文件访问域名
                ],
                'minio' => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\S3AdapterFactory::class,
                    'credentials' => [
                        'key' => 'S3_KEY',
                        'secret' => 'S3_SECRET',
                    ],
                    'region' => 'S3_REGION',
                    'version' => 'latest',
                    'bucket_endpoint' => false,
                    'use_path_style_endpoint' => true,
                    'endpoint' => 'S3_ENDPOINT',
                    'bucket_name' => 'S3_BUCKET',
                    'url' => '' // 静态文件访问域名
                ],
                'oss' => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\AliyunOssAdapterFactory::class,
                    'accessId' => 'OSS_ACCESS_ID',
                    'accessSecret' => 'OSS_ACCESS_SECRET',
                    'bucket' => 'OSS_BUCKET',
                    'endpoint' => 'OSS_ENDPOINT',
                    'url' => '', // 静态文件访问域名
                    'timeout' => 3600,
                    'connectTimeout' => 10,
                    'isCName' => false,
                    'token' => null,
                    'proxy' => null,
                ],
                'qiniu' => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\QiniuAdapterFactory::class,
                    'accessKey' => 'QINIU_ACCESS_KEY',
                    'secretKey' => 'QINIU_SECRET_KEY',
                    'bucket' => 'QINIU_BUCKET',
                    'domain' => 'QINBIU_DOMAIN',
                    'url' => '' // 静态文件访问域名
                ],
                'cos' => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\CosAdapterFactory::class,
                    'region' => 'COS_REGION',
                    'app_id' => 'COS_APPID',
                    'secret_id' => 'COS_SECRET_ID',
                    'secret_key' => 'COS_SECRET_KEY',
                    // 可选，如果 bucket 为私有访问请打开此项
                    'signed_url' => false,
                    'bucket' => 'COS_BUCKET',
                    'read_from_cdn' => false,
                    'url' => '' // 静态文件访问域名
                    // 'timeout' => 60,
                    // 'connect_timeout' => 60,
                    // 'cdn' => '',
                    // 'scheme' => 'https',
                ],
            ],
        ];
        $ModelConfig = ModelConfig::where(['group' => 'filesystem', 'channels_uid' => $request->channels_uid])->find();
        if ($ModelConfig) {
            $user = $ModelConfig->value;
            foreach ($user as $key => $value) {
                if ($key !== 'storage') {
                    $config[$key] = $value;
                }
            }
            foreach ($config['storage'] as $key => $value) {
                if (isset($user['storage'][$key])) {
                    $config['storage'][$key] = array_merge($config['storage'][$key], $user['storage'][$key]);
                }
            }
        }
        $builder = new FormBuilder(null, null, [
            'translations' => true,
            'submitEvent' => SubmitEvent::SILENT
        ]);
        $defaultOptions = Filesystem::getOptions(function ($item) {
            return !in_array($item['value'], [Filesystem::PUBLIC['value'], Filesystem::LOCAL['value']]);
        });
        $builder->add('default', '默认存储渠道商', 'select', $config['default'], [
            'options' => $defaultOptions
        ]);
        $builder->add('max_size', '单个文件大小(字节)', 'input-number', $config['max_size'], [
            'props' => [
                'min' => 0,
                'controls' => false,
                'style' => [
                    'width' => '200px'
                ]
            ]
        ]);
        $Component = new ComponentBuilder;

        $subBuilder = new FormBuilder('oss', '阿里云存储');
        $subBuilder->addValue('driver', \Shopwwi\WebmanFilesystem\Adapter\AliyunOssAdapterFactory::class);
        $subBuilder->add('accessId', 'AccessId', 'input', $config['storage']['oss']['accessId']);
        $subBuilder->add('accessSecret', 'AccessSecret', 'input', $config['storage']['oss']['accessSecret']);
        $subBuilder->add('bucket', 'Bucket', 'input', $config['storage']['oss']['bucket']);
        $subBuilder->add('endpoint', 'Bucket域名', 'input', $config['storage']['oss']['endpoint']);
        $subBuilder->add('isCName', '私有空间', 'switch', $config['storage']['oss']['isCName']);
        $subBuilder->add('url', '静态文件访问域名', 'input', $config['storage']['oss']['url'], [
            'prompt' => [
                $Component->add('text', ['default' => '对外访问域名，不以斜杠结尾'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => 'http://static.example.com'
            ]
        ]);
        $builder->addGroupForm($subBuilder);

        $subBuilder = new FormBuilder('cos', '腾讯云存储');
        $subBuilder->addValue('driver', \Shopwwi\WebmanFilesystem\Adapter\CosAdapterFactory::class);
        $subBuilder->add('app_id', 'Appid', 'input', $config['storage']['cos']['app_id']);
        $subBuilder->add('secret_id', 'SecretId', 'input', $config['storage']['cos']['secret_id']);
        $subBuilder->add('secret_key', 'SecretKey', 'input', $config['storage']['cos']['secret_key']);
        $subBuilder->add('region', 'Region', 'input', $config['storage']['cos']['region']);
        $subBuilder->add('bucket', 'Bucket', 'input', $config['storage']['cos']['bucket']);
        $subBuilder->add('read_from_cdn', '从CDN读取', 'switch', $config['storage']['cos']['read_from_cdn']);
        $subBuilder->add('signed_url', '私有空间', 'switch', $config['storage']['cos']['signed_url']);
        $subBuilder->add('url', '静态文件访问域名', 'input', $config['storage']['cos']['url'], [
            'prompt' => [
                $Component->add('text', ['default' => '对外访问域名，不以斜杠结尾'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => 'http://static.example.com'
            ]
        ]);
        $builder->addGroupForm($subBuilder);

        $subBuilder = new FormBuilder('qiniu', '七牛存储');
        $subBuilder->addValue('driver', \Shopwwi\WebmanFilesystem\Adapter\QiniuAdapterFactory::class);
        $subBuilder->add('accessKey', 'AccessKey', 'input', $config['storage']['qiniu']['accessKey']);
        $subBuilder->add('secretKey', 'SecretKey', 'input', $config['storage']['qiniu']['secretKey']);
        $subBuilder->add('bucket', 'Bucket', 'input', $config['storage']['qiniu']['bucket']);
        $subBuilder->add('domain', 'Domain', 'input', $config['storage']['qiniu']['domain']);
        $subBuilder->add('url', '静态文件访问域名', 'input', $config['storage']['qiniu']['url'], [
            'prompt' => [
                $Component->add('text', ['default' => '对外访问域名，不以斜杠结尾'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => 'http://static.example.com'
            ]
        ]);
        $builder->addGroupForm($subBuilder);

        $subBuilder = new FormBuilder('ftp', 'FTP存储');
        $subBuilder->addValue('driver', \Shopwwi\WebmanFilesystem\Adapter\FtpAdapterFactory::class);
        $subBuilder->add('host', 'HOST', 'input', $config['storage']['ftp']['host']);
        $subBuilder->add('username', 'UserName', 'input', $config['storage']['ftp']['username']);
        $subBuilder->add('password', 'Password', 'input', $config['storage']['ftp']['password']);
        $subBuilder->add('port', 'Port', 'input', $config['storage']['ftp']['port']);
        $subBuilder->add('root', '目录', 'input', $config['storage']['ftp']['root']);
        $subBuilder->add('passive', '被动模式', 'switch', $config['storage']['ftp']['passive']);
        $subBuilder->add('ssl', 'SSL', 'switch', $config['storage']['ftp']['ssl']);
        $subBuilder->add('timeout', '超时', 'input-number', $config['storage']['ftp']['timeout']);
        $subBuilder->add('url', '静态文件访问域名', 'input', $config['storage']['ftp']['url'], [
            'prompt' => [
                $Component->add('text', ['default' => '对外访问域名，不以斜杠结尾'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => 'http://static.example.com'
            ]
        ]);
        $builder->addGroupForm($subBuilder);

        $subBuilder = new FormBuilder('s3', 'S3存储');
        $subBuilder->addValue('driver', \Shopwwi\WebmanFilesystem\Adapter\S3AdapterFactory::class);
        $subBuilder->add('key', 'KEY', 'input', $config['storage']['s3']['credentials']['key']);
        $subBuilder->add('secret', 'Secret', 'input', $config['storage']['s3']['credentials']['secret']);
        $subBuilder->add('region', 'Region', 'input', $config['storage']['s3']['region']);
        $subBuilder->add('version', 'Version', 'input', $config['storage']['s3']['version']);
        $subBuilder->add('bucket_endpoint', 'Bucket Endpoint', 'switch', $config['storage']['s3']['bucket_endpoint']);
        $subBuilder->add('use_path_style_endpoint', 'Use path style endpoint', 'switch', $config['storage']['s3']['use_path_style_endpoint']);
        $subBuilder->add('endpoint', 'Endpoint', 'input', $config['storage']['s3']['endpoint']);
        $subBuilder->add('bucket_name', 'Bucket Name', 'input', $config['storage']['s3']['bucket_name']);
        $subBuilder->add('url', '静态文件访问域名', 'input', $config['storage']['s3']['url'], [
            'prompt' => [
                $Component->add('text', ['default' => '对外访问域名，不以斜杠结尾'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => 'http://static.example.com'
            ]
        ]);
        $builder->addGroupForm($subBuilder);

        $subBuilder = new FormBuilder('minio', 'Minio存储');
        $subBuilder->addValue('driver', \Shopwwi\WebmanFilesystem\Adapter\S3AdapterFactory::class);
        $subBuilder->add('key', 'KEY', 'input', $config['storage']['minio']['credentials']['key']);
        $subBuilder->add('secret', 'Secret', 'input', $config['storage']['minio']['credentials']['secret']);
        $subBuilder->add('region', 'Region', 'input', $config['storage']['minio']['region']);
        $subBuilder->add('version', 'Version', 'input', $config['storage']['minio']['version']);
        $subBuilder->add('bucket_endpoint', 'Bucket Endpoint', 'switch', $config['storage']['minio']['bucket_endpoint']);
        $subBuilder->add('use_path_style_endpoint', 'Use path style endpoint', 'switch', $config['storage']['minio']['use_path_style_endpoint']);
        $subBuilder->add('endpoint', 'Endpoint', 'input', $config['storage']['minio']['endpoint']);
        $subBuilder->add('bucket_name', 'Bucket Name', 'input', $config['storage']['minio']['bucket_name']);
        $subBuilder->add('url', '静态文件访问域名', 'input', $config['storage']['minio']['url'], [
            'prompt' => [
                $Component->add('text', ['default' => '对外访问域名，不以斜杠结尾'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => 'http://static.example.com'
            ]
        ]);
        $builder->addGroupForm($subBuilder);
        return $this->resData($builder);
    }
}
