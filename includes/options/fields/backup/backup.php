<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access directly.
/**
 *
 * Field: backup
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Field_backup')) {
    class OVIC_Field_backup extends OVIC_Fields
    {

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {
            $unique = (isset($this->field['unique'])) ? $this->field['unique'] : $this->unique;
            $nonce  = wp_create_nonce('ovic_backup_nonce');
            $export = add_query_arg(array(
                'action' => 'ovic-export',
                'unique' => $unique,
                'nonce'  => $nonce
            ), admin_url('admin-ajax.php'));

            echo $this->field_before();

            echo '<textarea name="ovic_import_data" class="ovic-import-data"></textarea>';
            echo '<button type="submit" class="button button-primary ovic-confirm ovic-import" data-unique="'.esc_attr($unique).'" data-nonce="'.esc_attr($nonce).'">'.esc_html__('Import', 'ovic-addon-toolkit').'</button>';
            echo '<small>( '.esc_html__('copy-paste your backup string here', 'ovic-addon-toolkit').' )</small>';

            echo '<hr />';
            echo '<textarea readonly="readonly" class="ovic-export-data">'.esc_attr(json_encode(get_option($unique))).'</textarea>';
            echo '<a href="'.esc_url($export).'" class="button button-primary ovic-export" target="_blank">'.esc_html__('Export and Download Backup', 'ovic-addon-toolkit').'</a>';

            echo '<hr />';
            echo '<button type="submit" name="ovic_transient[reset]" value="reset" class="button ovic-warning-primary ovic-confirm ovic-reset" data-unique="'.esc_attr($unique).'" data-nonce="'.esc_attr($nonce).'">'.esc_html__('Reset All', 'ovic-addon-toolkit').'</button>';
            echo '<small class="ovic-text-error">'.esc_html__('Please be sure for reset all of options.', 'ovic-addon-toolkit').'</small>';

            echo $this->field_after();

        }

    }
}
