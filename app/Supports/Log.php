<?php

namespace App\Supports;

use App\SERVICE;
use Illuminate\Support\Facades\DB;

class Log
{
    protected static $DELETED = "DELETED";
    protected static $CREATED = "CREATED";
    protected static $UPDATED = "UPDATED";
    protected static $CHANGED = "CHANGED";
    protected static $VIEW    = "VIEW";
    protected static $UPLOAD  = "UPLOAD";
    protected static $MOVE    = "MOVE";
    protected static $target
                              = [
            'categories' => 'Category',
            'countries'  => 'Country',
            'currencies' => 'Currency',
        ];

    /**
     * @param $target
     * @param null $description
     */
    static function view($target, $description = null)
    {
        self::process(self::$VIEW, $target, $description);
    }

    static function move($target, $description = null)
    {
        self::process(self::$MOVE, $target, $description);
    }

    static function upload($target, $description = null)
    {
        self::process(self::$UPLOAD, $target, $description);
    }

    /**
     * @param $target
     * @param null $description
     */
    static function create($target, $description = null)
    {
        self::process(self::$CREATED, $target, $description);
    }

    /**
     * @param $target
     * @param $old_data
     * @param $new_data
     */
    static function update($target, $description = null)
    {
        self::process(self::$UPDATED, $target, $description);
    }

    /**
     * @param $target
     * @param $deleted_data
     */
    static function delete($target, $description = null)
    {
        self::process(self::$DELETED, $target, $description);
    }

    /**
     * @param $target
     * @param $old_data
     * @param $new_data
     */
    static function change($target, $old_data, $new_data)
    {
        self::process(self::$CHANGED, $target, $old_data, $new_data);
    }

    /**
     * @param      $action
     * @param      $old_data
     * @param null $new_data
     */
    private static function process($action, $target, $description = null)
    {
        $user_id = SERVICE::getCurrentUserId();
        $now     = date('Y-m-d H:i:s', time());
        /* $browser = get_browser(null, true);
         $parent = array_get($browser, 'parent', '');
         $platform = array_get($browser, 'platform', '');
         $browser = array_get($browser, 'browser', '');*/
        DB::table('user_logs')->insert([
            'action'      => $action,
            'target'      => "Table: $target",
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'description' => $description,
            'created_at'  => $now,
            'created_by'  => $user_id,
            'updated_at'  => $now,
            'updated_by'  => $user_id,
        ]);
    }

    public static function message($user_name, $method, $target, $description = null)
    {
        $target = strtolower(trim(trim($target, 'Table:')));
        $target = !empty(self::$target[$target]) ? self::$target[$target] : $target;

        return str_replace("  ", " ", "<strong>" . trim($user_name) . "</strong>" . " " . trim(" " .
                    Message::get("L-" . $method) . " " . $target)) . "<br />" . $description . " <span class='text-danger'><i>(" . strtolower($method) . ")</i></span>";
    }
}