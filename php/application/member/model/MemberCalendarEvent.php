<?php
namespace app\member\model;

class MemberCalendarEvent extends MemberBaseModel{

    // 阴历所有年份, 天干地支
    const YIN_YEARS = ['甲子','甲戌','甲申','甲午','甲辰','甲寅',' 乙丑','乙亥','乙酉','乙未','乙巳','乙卯',' 丙寅','丙子','丙戌','丙申','丙午','丙辰',' 丁卯','丁丑','丁亥','丁酉','丁未','丁巳',' 戊辰','戊寅','戊子','戊戌','戊申','戊午',' 己巳','己卯','己丑','己亥','己酉','己未',' 庚午','庚辰','庚寅','庚子','庚戌','庚申',' 辛未','辛巳','辛卯','辛丑','辛亥','辛酉',' 壬申','壬午','壬辰','壬寅','壬子','壬戌',' 癸酉','癸未','癸巳','癸卯','癸丑','癸亥'];

    // 阴历所有的月份
    const YIN_MONTHS = ['正月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '冬月', '腊月'];

    // 阴历所有日期
    const YIN_DAYS = ['初一', '初二', '初三', '初四', '初五', '初六', '初七', '初八', '初九', '初十', '十一', '十二', '十三', '十四', '十五', '十六', '十七', '十八', '十九', '二十', '廿一', '廿二', '廿三', '廿四', '廿五', '廿六', '廿七', '廿八', '廿九', '三十'];

    // 事件内容类型字典
    const CONTENT_TYPE_MAP = [
        0, //       =>      '普通文本',
        1, //       =>      'HTML格式',
        2, //       =>      'JSON格式',
        3, //       =>      'RM格式'
    ];

    // 设置当前模型对应的完整数据表名称
    protected $table = 'as_member_calendar_event';

    // 默认主键
    protected $pk = 'id';
    
    // 只读字段
    protected $readonly = ['create_by', 'create_time'];

    protected static function init()
    {
        self::beforeInsert(function ($model) {
            self::filterFields($model);
        });
        self::beforeUpdate(function ($model) {
            self::filterFields($model);
        });
    }

    protected static function filterFields($model){
        $model
        ->limitLength(['name', ], 64)
        ->limitLength(['content', ], 65535)
        ->limitLength(['remark', ], 3072)
        ->parseFieldsInArray(['content_type'], [0, 1, 2, 3])
        ->parseFieldsInArray(['disabled'], [0, 1]);
    }

}