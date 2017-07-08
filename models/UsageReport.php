<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usage_report".
 *
 * @property string $id
 * @property integer $batch_number
 * @property string $mas_code
 * @property string $phone
 * @property string $response
 * @property integer $location_id
 * @property string $date_reported
 * @property string $pin_4_digits
 * @property string $created_date
 * @property integer $created_by
 * @property string $modified_date
 * @property integer $modified_by
 *
 * @property Complaint[] $complaints
 * @property Location $location
 */
class UsageReport extends \yii\db\ActiveRecord
{
    public $geozone_id;
    public $state_id;
    public $lga_id;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'usage_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_number', 'phone', 'response', 'location_id', 'date_reported', 'pin_4_digits'], 'required'],
            [['batch_number', 'response', 'location_id', 'created_by', 'modified_by'], 'integer'],
            [['location_id', 'response'], 'integer', 'min' => 1],
            [['date_reported', 'created_date', 'modified_date'], 'safe'],
            [['phone'], 'string', 'max' => 11],
            [['batch_number'], 'unique'],
            [['phone'], 'match', 'pattern' => '/^[0-9]+$/'],
            [['pin_4_digits'], 'string', 'max' => 4],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Location::className(), 'targetAttribute' => ['location_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_number' => 'Batch Number',
            'phone' => 'Phone',
            'response' => 'Response',
            'location_id' => 'Location ID',
            'date_reported' => 'Date Reported',
            'pin_4_digits' => 'Pin Last 4 Digits',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
            'modified_date' => 'Modified Date',
            'modified_by' => 'Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComplaints()
    {
        return $this->hasMany(Complaint::className(), ['report_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Location::className(), ['id' => 'location_id']);
    }
}