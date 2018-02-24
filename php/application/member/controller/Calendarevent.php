<?php
namespace app\member\controller;

use app\member\model\Member as MemberModel;
use app\member\model\MemberCalendarEvent as MemberCalendarEventModel;

use allensnape\utils\StringUtil;

class Calendarevent extends MemberBaseController{

    /**
     * 获取事件json列表
     */
    public function jsonList(){
        $member = MemberModel::getCurrentMember();
        $events = new MemberCalendarEventModel($_POST);
        return $this->json_success('获取成功!', $events->getStandardPagedArrayList([
                [['name', 'content']], 
                [['year', 'month', 'week', 'day', 'hour', 'minute', 'second', 'yin_year', 'yin_month', 'yin_day'], '', '', '='], 
                [['disabled'], '', '', '=', 0],
                [['member_id'], '', '', '=', $member['id']]
            ], ['year', 'month', 'week', 'day', 'hour', 'minute', 'second', 'yin_year', 'yin_month', 'yin_day', 'create_time', 'update_time'])
        );
    }

    /**
     * 添加事件
     */
    public function add(){
        $member = MemberModel::getCurrentMember();
        $event = new MemberCalendarEventModel($_POST);

        // 检查数据
        if(!$this->hasText($event['name'])){
            return $this->json_error('请填写事件的名称!');
        }
        else if(!in_array($event['content_type'], MemberCalendarEventModel::CONTENT_TYPE_MAP)){
            return $this->json_error('内容参数类型错误!');
        }
        // 标记的时间必须存在一个
        else if(
            !is_numeric($event['year']) &&
            !is_numeric($event['month']) &&
            !is_numeric($event['week']) &&
            !is_numeric($event['day']) &&
            !is_numeric($event['hour']) &&
            !is_numeric($event['minute']) &&
            !is_numeric($event['second']) &&
            !$this->hasText($event['yin_year']) &&
            !$this->hasText($event['yin_month']) &&
            !$this->hasText($event['yin_day'])
        ){
            return $this->json_error('事件触发时间必须存在一种!');
        }
        // 检查年, 不得为负数(因为不记录公元前的定时事件)
        if(is_numeric($event['year']) && $event['year'] < 0){
            return $this->json_error('触发日期-年: 不得为负数(或不得小于今年)!');
        }
        // 检查月, 区间为1-12
        if(is_numeric($event['month']) && ($event['month'] < 1 || $event['month'] > 12)){
            return $this->json_error('触发日期-月: 仅限1-12!');
        }
        // 检查星期, 区间1-7
        if(is_numeric($event['week']) && ($event['week'] < 1 || $event['week'] > 7)){
            return $this->json_error('触发日期-月: 仅限1-7!');
        }
        // 检查日, 根据不同月份(如果存在)和不同年份(闰年平年, 如果存在)进行判断
        if(is_numeric($event['day'])){
            // 如果存在月份
            if(is_numeric($event['month'])){
                $maxday = 31;
                switch($event['month']){
                    case 2: {
                        if(is_numeric($event['year'])){
                            if(
                                // 不能被100整除但能被4整除
                                ($event['year'] % 100 != 0 && $event['year'] % 4 == 0) || 
                                // 能被100整除且能被400整除
                                ($event['year'] % 100 == 0 && $event['year'] % 400 == 0) || 
                                // 能被3200整除且能被172800整除
                                ($event['year'] % 3200 == 0 && $event['year'] % 172800 == 0)
                            ){
                                $maxday = 29;
                            }else{
                                $maxday = 28;
                            }
                        }else{
                            $maxday = 29;
                        }
                        break;
                    }
                    case 4:
                    case 6:
                    case 9:
                    case 11: $maxday = 30; break;
                    default: $maxday = 31; break;
                }

                if($event['day'] < 1 || $event['day'] > $maxday){
                    return $this->json_error('当前组合时间的日期需小于等于'.$maxday.'且大于等于1!');
                }
            }
            // 不存在则设置区间为: 1-31
            else{
                if($event['day'] < 1 || $event['day'] > 31){
                    return $this->json_error('触发日期-日: 仅限1-31!');
                }
            }
        }
        // 检查小时, 区间0-23
        if(is_numeric($event['hour']) && ($event['hour'] < 0 || $event['hour'] > 23)){
            return $this->json_error('触发时间-时: 仅限0-24!');
        }
        // 检查分钟, 区间0-59
        if(is_numeric($event['minute']) && ($event['minute'] < 0 || $event['minute'] > 59)){
            return $this->json_error('触发时间-分: 仅限0-59!');
        }
        // 检查秒, 区间0-59
        if(is_numeric($event['second']) && ($event['second'] < 0 || $event['second'] > 59)){
            return $this->json_error('触发时间-秒: 仅限0-59!');
        }
        // 检查农历日期-年, 区间MemberCalendarEventModel::YIN_YEARS
        if($this->hasText($event['yin_year']) && !in_array($event['yin_year'], MemberCalendarEventModel::YIN_YEARS)){
            return $this->json_error('触发时间-农历年, 仅限天干地支年(甲子、甲戌、甲申、甲午、甲辰、甲寅、 乙丑、乙亥、乙酉、乙未、乙巳、乙卯、 丙寅、丙子、丙戌、丙申、丙午、丙辰、 丁卯、丁丑、丁亥、丁酉、丁未、丁巳、 戊辰、戊寅、戊子、戊戌、戊申、戊午、 己巳、己卯、己丑、己亥、己酉、己未、 庚午、庚辰、庚寅、庚子、庚戌、庚申、 辛未、辛巳、辛卯、辛丑、辛亥、辛酉、 壬申、壬午、壬辰、壬寅、壬子、壬戌、 癸酉、癸未、癸巳、癸卯、癸丑、癸亥)!');
        }
        // 检查农历日期-月, 区间MemberCalendarEventModel::YIN_MONTHS
        if($this->hasText($event['yin_month']) && !in_array($event['yin_month'], MemberCalendarEventModel::YIN_MONTHS)){
            return $this->json_error('触发时间-农历月, 仅限正月、二月、三月、四月、五月、六月、七月、八月、九月、十月、冬月、腊月!');
        }
        // 检查农历日期-日, 区间MemberCalendarEventModel::YIN_DAYS, 不检查是否与月份对应, 如果出现不可能日期就当冗余数据处理
        if($this->hasText($event['yin_day']) && !in_array($event['yin_day'], MemberCalendarEventModel::YIN_DAYS)){
            return $this->json_error('触发时间-农历日, 仅限初一、初二、初三、初四、初五、初六、初七、初八、初九、初十、十一、十二、十三、十四、十五、十六、十七、十八、十九、二十、廿一、廿二、廿三、廿四、廿五、廿六、廿七、廿八、廿九、三十!');
        }

        $event['member_id'] = $member['id'];
        $event['disabled'] = 0;

        return $event->beforeSave()->allowField([
            'id', 
            'member_id', 
            'name', 
            'content_type', 
            'content', 
            'year', 
            'month', 
            'week', 
            'day', 
            'hour', 
            'minute', 
            'second', 
            'yin_year', 
            'yin_month', 
            'yin_day', 
            'remark', 
            'create_time', 
            'update_time'
        ])->save() ? $this->json_success('添加成功!', $event->getData()) : $this->json_error('添加失败!');
    }

}