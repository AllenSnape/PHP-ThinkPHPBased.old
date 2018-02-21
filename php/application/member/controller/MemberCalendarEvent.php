<?php
namespace app\member\controller;

use app\member\model\Member;

use allensnape\utils\StringUtil;

class MemberCalendarEvent extends MemberBaseController{

    /**
     * 获取事件json列表
     */
    public function jsonList(){
        $member = Member::getCurrentMember();

    }

}