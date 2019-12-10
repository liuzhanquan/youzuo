<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/7
 * Time: 14:08
 */

namespace app\api\controller\v1;
use app\common\model\Category;
use app\api\controller\Api;
use app\common\model\FriendCollection;
use app\common\model\FriendLike;
use app\common\model\User;
use hg\Code;
use hg\ServerResponse;
use \app\common\model\Photo;
use app\common\model\Video;
use app\common\model\VideoComment;
use app\common\model\VideoLike;
use app\common\model\Friend;
use think\Db;

class Material extends Api
{
    /**
     * 素材列表
     * @param Category $category
     * @return \think\response\Json
     */
    public function index(Category $category,Photo $photo){
        try{
            $cid = $this->data['cid'] ?? 0;
            $where = [];
            if($cid){
                $where['cid'] = $cid;
            }
            $list = Photo::where($where)->paginate(12,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $data[$key]['image'] = request()->domain().str_replace("\\", '/', $value['image']);
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }
    /**
     * 获取素材分类
     */
    public function category(Category $category){
        try{
            $type = $this->data['type'];
            $name = $this->data['name'] ?? '';
            $where = [];
            if($name){
                $where[] = ['name','like',"%{$name}%"];
            }
            $list = $category->where(['type'=>$type,'parent_id'=>0])->order('sort asc')->select();
            $data = $list;
            if($type == 2){
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
            }
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $arr = Category::where(['type'=>$type,'parent_id'=>$value['id']])->order('sort asc')->select();
                    if(!empty($arr)){
                        foreach ($arr as $k=>$v){
                            $where = [];
                            if($name){
                                $where[] = ['name','like',"%{$name}%"];
                            }
                            $where[] = ['type','=',$type];
                            $where[] = ['parent_id','=',$v['id']];
                            $newArr = Category::where($where)->order('sort asc')->select();
                                if(!empty($newArr)){
                                    foreach ($newArr as $kk=>$vv){
                                        $newArr[$kk]['image'] = request()->domain().str_replace("\\", '/', $vv['image']);
                                    }
                                }

                            $arr[$k]['list'] = $newArr;
                        }
                    }
                    $data[$key]['list'] = $arr;
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 搜索分类
     */
    public function category2(Category $category){
        try{
            $type = $this->data['type'];
            $name = $this->data['name'] ?? '';
            $where[] = ['level','=',3];
            $where[] = ['type','=',$type];
            if($name){
                $where[] = ['name','like',"%{$name}%"];
            }
            $list = $category->where($where)->order('sort asc')->select();
            $data = $list;
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $data[$key]['image'] = request()->domain().str_replace("\\", '/', $value['image']);
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 获取视频所有的月份
     */
    public function getDateList(Video $video){
        try{
            $list = $video->field('datetype')->group('datetype')->select();
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 获取视频列表
     */
    public function video(Video $video){
        try{
            $title= $this->data['date'] ?? '';
            $cid = $this->data['cid'] ?? '';
            $where = [];
            if($title){
                $where[] = ['datetype','=',$title];
            }
            if($cid){
                //把所有子级分类显示出来
                $where[] = ['cid','in',implode(',',$this->getCate($cid))];
            }
            $list = $video->where($where)->order('sort asc')->paginate(10,false,['query'=>request()->param()])->toArray();;
            $data = $list['data'];
            $videoCnfig = unserialize(Db::name('config')->where('name','upload')->value('value'));
            if($videoCnfig['type'] == '2') { // 开启七牛云后}
                $host = $videoCnfig['domain'];
            }else{
                $host = request()->domain();
            }
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $data[$key]['image'] = $videoCnfig['domain'].$value['video'].str_replace("\\", '/', $value['image']);
                    $data[$key]['video'] = $host.str_replace("\\", '/', $value['video']);
                    $data[$key]['cate_name'] = Category::where(['id'=>$value['cid']])->value('name');
                    $data[$key]['date'] = date('Y-m-d',strtotime($value['timestamp']));
                }
            }
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 获取分类下面的所有子分类
     * @param $cid
     */
    protected function getCate($cid){
        $info = Db::name('category')->where('id',$cid)->field('parent_id,level')->find();
        $cate = Db::name('category')->where('parent_id',$cid)->field('parent_id,level,id')->select();
        $arr[] = $cid;
        if(!empty($cate) && $info['level'] < 4){
            foreach ($cate as $key=>$value){
                $arr = array_merge($this->getCate($value['id']),$arr);
            }
        }
        return $arr;
    }

    /**
     * 获取视频详情
     */
    public function videoDetails(Video $video,VideoLike $videoLike){
        try{
            $id= $this->data['id'] ?? '';
            $info = $video->where(['id'=>$id])->field('view_num,id,image,video')->find();
            $like = $videoLike->where(['user_id'=>$this->uid,'video_id'=>$id])->field('id')->find();
            $videoCnfig = unserialize(Db::name('config')->where('name','upload')->value('value'));
            if(!$like){
                $info['is_like'] = 1;
            }else{
                $info['is_like'] = 2;
            }
            $info['image'] = $videoCnfig['domain'].$info['video'].str_replace("\\", '/', $info['image']);
            $info['video'] = request()->domain().str_replace("\\", '/', $info['video']);

            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$info]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     *  创建视频评论
     */
    public function videoComment(VideoComment $videoComment){
        try{
            $content = $this->data['content'];
            if(!$content){
                return json(['StatusCode'=>50000,'message'=>'请输入评论内容']);
            }
            $this->data['user_id'] = $this->uid;
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            $this->data['created_at'] = date('Y-m-d H:i:s');
            if(!$videoComment->allowField(true)->save($this->data)){
                return json(['StatusCode'=>50000,'message'=>'发布失败']);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     *  视频评论列表
     */
    public function videoCommentList(VideoComment $videoComment){
        try{
            $video_id = $this->data['video_id'];
            $where['video_id'] = $video_id;
            $where['status'] = 1;
            $list = $videoComment->where($where)->order('created_at desc')->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $user = User::where(['uid'=>$value['user_id']])->field('nickname,headimgurl')->find();
                    $data[$key]['nickname'] = $user['nickname'];
                    $data[$key]['headimgurl'] = $user['headimgurl'];
                    $data[$key]['created_at_str'] = format_datetime($value['created_at'],1);
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     *  视频点赞/取消点赞
     */
    public function videoLike(VideoLike $videoLike){
        try{
            $video_id = $this->data['video_id'];
            if(!$video_id){
                return json(['StatusCode'=>50000,'message'=>'参数错误']);
            }
            $info = $videoLike->where(['video_id'=>$video_id,'user_id'=>$this->uid])->find();
            if($info){
                VideoLike::where(['video_id'=>$video_id,'user_id'=>$this->uid])->delete();
            }else{
                VideoLike::create(['video_id'=>$video_id,'user_id'=>$this->uid,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 朋友圈素材
     */
    public function friend(Friend $friend){
        try{
            $type = $this->data['type'] ?? '';
            $title = $this->data['title'] ?? '';
            $where = [];
            if($type){
                $where[] = ['type','=',$type];
            }
            if($title){
                $where[] = ['content','like',"%{$title}%"];
            }
            $list = $friend->where($where)->order('sort asc')->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $image = [];
                    if($value['type'] == 1){
                        $image = unserialize($value['image']);
                        if(!empty($image)){
                            foreach ($image as $k=>$v){
                                $image[$key] = request()->domain().str_replace("\\", '/', $v);
                            }
                        }
                    }
                    //判断当前用户是否收藏或者点赞
                    if(FriendLike::where(['user_id'=>$this->uid,'friend_id'=>$value['id']])->count('id')){
                        $data[$key]['is_like'] = 1;
                    }else{
                        $data[$key]['is_like'] = 2;
                    }
                    if(FriendCollection::where(['user_id'=>$this->uid,'friend_id'=>$value['id']])->count('id')){
                        $data[$key]['is_collection'] = 1;
                    }else{
                        $data[$key]['is_collection'] = 2;
                    }
                    $data[$key]['image'] = $image;
                    $data[$key]['video'] = request()->domain().str_replace("\\", '/', $value['video']);
                    $data[$key]['created_at_str'] = format_datetime($value['timestamp'],1);
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 朋友圈素材点赞/收藏
     */
    public function friendLike(Friend $friend,FriendLike $friendLike){
        try{
            $type = $this->data['type'] ?? 1;
            $id = $this->data['id'];
            if($type == 1){
                if(FriendLike::where(['user_id'=>$this->uid,'friend_id'=>$id])->count('id')){
                    return json(['StatusCode'=>50000,'message'=>'已经点赞']);
                }
                //点赞
                $res = $friendLike->save(['user_id'=>$this->uid,'friend_id'=>$id,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                $friend->where(['id'=>$id])->setInc('like_num');
            }else{
                //取消点赞
                $res = $friendLike->where(['user_id'=>$this->uid,'friend_id'=>$id])->delete();
                $friend->where(['id'=>$id])->setDec('like_num');
            }
            if(!$res){
                return json(['StatusCode'=>50000,'message'=>'请求失败']);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 朋友圈素材收藏
     */
    public function friendCollection(Friend $friend,FriendCollection $friendCollection){
        try{
            $type = $this->data['type'] ?? 1;
            $id = $this->data['id'];
            if($type == 1){
                if(FriendCollection::where(['user_id'=>$this->uid,'friend_id'=>$id])->count('id')){
                    return json(['StatusCode'=>50000,'message'=>'已经点赞']);
                }
                //收藏
                $res = $friendCollection->save(['user_id'=>$this->uid,'friend_id'=>$id,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                $friend->where(['id'=>$id])->setInc('collection_num');
            }else{
                //取消收藏
                $res = $friendCollection->where(['user_id'=>$this->uid,'friend_id'=>$id])->delete();
                $friend->where(['id'=>$id])->setDec('collection_num');
            }
            if(!$res){
                return json(['StatusCode'=>50000,'message'=>'请求失败']);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }
}