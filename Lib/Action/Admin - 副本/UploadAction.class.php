<?php
/**
 * 文件上传
 * @author Gavin
 * @version 1.0
 */
class UploadAction extends AdminBaseAction {

    public function __construct(){
        parent::__construct();
    }


    public function ajaxUpload(){
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 10485760 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型

		$upload->savePath = APP_PATH.'uploads/'.date('Ymd').'/';
		// 设置附件上传目录
		$upload->saveRule = time().'_'.mt_rand();
		
		$upload->thumb = true;
		$upload->thumbMaxWidth = '2000';
		$upload->thumbMaxHeight = '2000';
		$upload->thumbRemoveOrigin = true;
		
		//$upload->thumbMaxHeight = '50,200';
		
		if(!$upload->upload()) {// 上传错误提示错误信息
			//$this->ajaxReturn($upload->getErrorMsg());
//			$this->ajaxReturn(2);
			$data['error'] = 1;
			$data['info'] = $upload->getErrorMsg();
			$this->ajaxReturn($data,'JSON');
		}
		$info =  $upload->getUploadFileInfo();
        $info[0]['savepath'] = '/uploads/'.date('Ymd').'/';
		$data['error'] = 0;
		$data['info'] = $info;
		$this->ajaxReturn($data,'JSON');
		
		// 保存表单数据 包括附件数据
// 		$User = M("User"); // 实例化User对象
// 		$User->create(); // 创建数据对象
// 		$User->photo = $info[0]['savename']; // 保存上传的照片根据需要自行组装
// 		$User->add(); // 写入用户数据到数据库
//		$this->ajaxReturn(1);
	}

    public function ajaxUploadAttachment(){
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 30485760 ;// 设置附件上传大小
		$upload->allowExts  = array('doc', 'docx', 'xls', 'xlsx','ppt','pptx','txt','zip','rar','7z','pdf');// 设置附件上传类型
		$upload->savePath = './uploads/attachment/';// 设置附件上传目录
		$upload->saveRule = time().'_'.mt_rand();
		
		if(!$upload->upload()) {// 上传错误提示错误信息
			//$this->ajaxReturn($upload->getErrorMsg());
//			$this->ajaxReturn(2);
			$data['error'] = 1;
			$data['info'] = $upload->getErrorMsg();
			$this->ajaxReturn($data,'JSON');
		}
		$info =  $upload->getUploadFileInfo();
		$data['error'] = 0;
		$data['info'] = $info;

		$this->ajaxReturn($data,'JSON');

		
		// 保存表单数据 包括附件数据
// 		$User = M("User"); // 实例化User对象
// 		$User->create(); // 创建数据对象
// 		$User->photo = $info[0]['savename']; // 保存上传的照片根据需要自行组装
// 		$User->add(); // 写入用户数据到数据库
//		$this->ajaxReturn(1);
	}
    public function ajaxUploadAttachment2(){
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = 30485760 ;// 设置附件上传大小
        $upload->allowExts  = array('doc', 'docx', 'xls', 'xlsx','ppt','pptx','txt','zip','rar','7z','pdf');// 设置附件上传类型
        $upload->savePath = './uploads/attachment/';// 设置附件上传目录
        $upload->saveRule = time().'_'.mt_rand();

        if(!$upload->upload()) {// 上传错误提示错误信息
            //$this->ajaxReturn($upload->getErrorMsg());
//			$this->ajaxReturn(2);
            $data['error'] = 1;
            $data['info'] = $upload->getErrorMsg();
            $this->ajaxReturn($data,'JSON');
        }
        $info =  $upload->getUploadFileInfo();
        $data['error'] = 0;
        $data['info'] = $info;

        $this->ajaxReturn($data,'JSON');


        // 保存表单数据 包括附件数据
// 		$User = M("User"); // 实例化User对象
// 		$User->create(); // 创建数据对象
// 		$User->photo = $info[0]['savename']; // 保存上传的照片根据需要自行组装
// 		$User->add(); // 写入用户数据到数据库
//		$this->ajaxReturn(1);
    }
    public function ajaxUploadAttachment3(){
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = 30485760 ;// 设置附件上传大小
        $upload->allowExts  = array('doc', 'docx', 'xls', 'xlsx','ppt','pptx','txt','zip','rar','7z','pdf');// 设置附件上传类型
        $upload->savePath = './uploads/attachment/';// 设置附件上传目录
        $upload->saveRule = time().'_'.mt_rand();

        if(!$upload->upload()) {// 上传错误提示错误信息
            //$this->ajaxReturn($upload->getErrorMsg());
//			$this->ajaxReturn(2);
            $data['error'] = 1;
            $data['info'] = $upload->getErrorMsg();
            $this->ajaxReturn($data,'JSON');
        }
        $info =  $upload->getUploadFileInfo();
        $data['error'] = 0;
        $data['info'] = $info;

        $this->ajaxReturn($data,'JSON');


        // 保存表单数据 包括附件数据
// 		$User = M("User"); // 实例化User对象
// 		$User->create(); // 创建数据对象
// 		$User->photo = $info[0]['savename']; // 保存上传的照片根据需要自行组装
// 		$User->add(); // 写入用户数据到数据库
//		$this->ajaxReturn(1);
    }
    public function ajaxUploadAttachment4(){
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = 30485760 ;// 设置附件上传大小
        $upload->allowExts  = array('doc', 'docx', 'xls', 'xlsx','ppt','pptx','txt','zip','rar','7z','pdf');// 设置附件上传类型
        $upload->savePath = './uploads/attachment/';// 设置附件上传目录
        $upload->saveRule = time().'_'.mt_rand();

        if(!$upload->upload()) {// 上传错误提示错误信息
            //$this->ajaxReturn($upload->getErrorMsg());
//			$this->ajaxReturn(2);
            $data['error'] = 1;
            $data['info'] = $upload->getErrorMsg();
            $this->ajaxReturn($data,'JSON');
        }
        $info =  $upload->getUploadFileInfo();
        $data['error'] = 0;
        $data['info'] = $info;

        $this->ajaxReturn($data,'JSON');


        // 保存表单数据 包括附件数据
// 		$User = M("User"); // 实例化User对象
// 		$User->create(); // 创建数据对象
// 		$User->photo = $info[0]['savename']; // 保存上传的照片根据需要自行组装
// 		$User->add(); // 写入用户数据到数据库
//		$this->ajaxReturn(1);
    }
          public function  fileupload(){

              //header("Content-Type: text/html; charset=utf-8");
             //include "Uploader.class.php";
              //上传图片框中的描述表单名称，
              $title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
              $path = htmlspecialchars($_POST['dir'], ENT_QUOTES);

              //上传配置
              $config = array(
                  "savePath" => (APP_PATH.'uploads/upload/'),
                  "maxSize" => 1000000, //单位KB
                  "allowFiles" => array(".gif", ".png", ".jpg", ".jpeg", ".bmp" ,'.doc', '.docx', '.xls', '.xlsx','.ppt','.pptx','.txt','.zip','.rar','.7z','.pdf')// 设置附件上传类型,

              );

              //生成上传实例对象并完成上传
              $up = new Uploader("upfile", $config);

              /**
               * 得到上传文件所对应的各个参数,数组结构
               * array(
               *     "originalName" => "",   //原始文件名
               *     "name" => "",           //新文件名
               *     "url" => "",            //返回的地址
               *     "size" => "",           //文件大小
               *     "type" => "" ,          //文件类型
               *     "state" => ""           //上传状态，上传成功时必须返回"SUCCESS"
               * )
               */
              $info = $up->getFileInfo();

              /**
               * 向浏览器返回数据json数据
               * {
               *   'url'      :'a.jpg',   //保存后的文件路径
               *   'title'    :'hello',   //文件描述，对图片来说在前端会添加到title属性上
               *   'original' :'b.jpg',   //原始文件名
               *   'state'    :'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
               * }
               */
//              "{'url':'" . $info["url"] . "','title':'" . $title . "','original':'" . $info["originalName"] . "','state':'" . $info["state"] . "'}";
              $data = array('url' => $info['url'],'title' => $title , 'original' => $info["originalName"] , 'state' =>   $info["state"]);
              $this->ajaxReturn($data);

          }


    public function  remoteImage(){

        $config = array(
            "savePath" => APP_PATH."uploads/" ,            //保存路径
            "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" ) , //文件允许格式
            "maxSize" => 3000                    //文件大小限制，单位KB
        );
        $uri = htmlspecialchars( $_POST[ 'upfile' ] );
        $uri = str_replace( "&amp;" , "&" , $uri );
        $this->getRemoteImage( $uri,$config );

    }

    private function getRemoteImage( $uri,$config)
    {
        //忽略抓取时间限制
        set_time_limit( 0 );
        //ue_separate_ue  ue用于传递数据分割符号
        $imgUrls = explode( "ue_separate_ue" , $uri );
        $tmpNames = array();
        foreach ( $imgUrls as $imgUrl ) {
            //http开头验证
            if(strpos($imgUrl,"http")!==0){
                array_push( $tmpNames , "error" );
                continue;
            }
            //获取请求头
            $heads = get_headers( $imgUrl );
            //死链检测
            if ( !( stristr( $heads[ 0 ] , "200" ) && stristr( $heads[ 0 ] , "OK" ) ) ) {
                array_push( $tmpNames , "error" );
                continue;
            }

            //格式验证(扩展名验证和Content-Type验证)
            $fileType = strtolower( strrchr( $imgUrl , '.' ) );
            if ( !in_array( $fileType , $config[ 'allowFiles' ] ) || stristr( $heads[ 'Content-Type' ] , "image" ) ) {
                array_push( $tmpNames , "error" );
                continue;
            }

            //打开输出缓冲区并获取远程图片

            if(0){
                $ch=curl_init();
                $timeout=5;
                curl_setopt($ch,CURLOPT_URL,$imgUrl);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $img=curl_exec($ch);
                curl_close($ch);
            }else{
                ob_start();
                $context = stream_context_create(
                    array (
                        'http' => array (
                            'follow_location' => false // don't follow redirects
                        )
                    )
                );
                //请确保php.ini中的fopen wrappers已经激活
                readfile( $imgUrl,false,$context);
                $img = ob_get_contents();
                ob_end_clean();
            }

            //大小验证
            $uriSize = strlen( $img ); //得到图片大小
            $allowSize = 1024 * $config[ 'maxSize' ];
            if ( $uriSize > $allowSize ) {
                array_push( $tmpNames , "error" );
                continue;
            }
            //创建保存位置
            $savePath = $config[ 'savePath' ];
            if ( !file_exists( $savePath ) ) {
                mkdir( "$savePath" , 0777 );
            }
            //写入文件
            $tmpName =  rand( 1 , 10000 ) . time() . strrchr( $imgUrl , '.' );
            try {
                $fp2 = @fopen( $savePath.$tmpName , "a" );
                fwrite( $fp2 , $img );
                fclose( $fp2 );
                array_push( $tmpNames ,  $tmpName );
            } catch ( Exception $e ) {
                array_push( $tmpNames , "error" );
            }
        }
        /**
         * 返回数据格式
         * {
         *   'url'   : '新地址一ue_separate_ue新地址二ue_separate_ue新地址三',
         *   'srcUrl': '原始地址一ue_separate_ue原始地址二ue_separate_ue原始地址三'，
         *   'tip'   : '状态提示'
         * }
         */
        $data = array( 'url' => implode( "ue_separate_ue" , $tmpNames ), 'tip' => '远程图片抓取成功' ,'srcUrl' =>$uri );
        //echo "{'url':'" . implode( "ue_separate_ue" , $tmpNames ) . "','tip':'远程图片抓取成功！','srcUrl':'" . $uri . "'}";
        $this->ajaxReturn($data);
    }
    private function get_domain()
    {
        /* 协议 */
        $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

        /* 域名或IP地址 */
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
        {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        }
        elseif (isset($_SERVER['HTTP_HOST']))
        {
            $host = $_SERVER['HTTP_HOST'];
        }
        else
        {
            /* 端口 */
            if (isset($_SERVER['SERVER_PORT']))
            {
                $port = ':' . $_SERVER['SERVER_PORT'];

                if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
                {
                    $port = '';
                }
            }
            else
            {
                $port = '';
            }

            if (isset($_SERVER['SERVER_NAME']))
            {
                $host = $_SERVER['SERVER_NAME'] . $port;
            }
            elseif (isset($_SERVER['SERVER_ADDR']))
            {
                $host = $_SERVER['SERVER_ADDR'] . $port;
            }
        }

        return $protocol . $host;
    }

}