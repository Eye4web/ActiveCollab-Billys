<?php

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 20/01/14
 * Time: 11.06
 */
class BillysJobType extends DataObject
{
    public function __construct($id = null, $jobTypeId = null, $projectId = null)
    {
        if (!$id) { //had to do this. Otherwise it was always giving an exception. Creating with null gives an error on save
            DB::execute('INSERT INTO ' . TABLE_PREFIX . $this->table_name . ' (job_type_id, project_id) VALUES(?, ?)', $jobTypeId, $projectId);
            $id = DB::lastInsertId();
        }
        parent::__construct($id);
    }

    protected $table_name = 'billys_job_types';

    /**
     * Table fields of billys_job_type
     * @var array
     */
    protected $fields = array(
        'id',
        'job_type_id',
        'project_id',
        'productId'
    );

    /**
     * Primary key
     * @var array
     */
    protected $primary_key = array('id');


    function getModelName($underscore = false, $singular = false)
    {
        return $underscore ? 'billys_job_type' : 'BillysJobType';
    } // getModelName

    /**
     * Return name of the table where system will persist model instances
     *
     * @param boolean $with_prefix
     * @return string
     */
    function getTableName($with_prefix = true)
    {
        return $with_prefix ? TABLE_PREFIX . $this->table_name : $this->table_name;
    } // getTableNa

    /**
     * @return int|mixed
     */
    function getId()
    {
        return $this->getFieldValue('id');
    }

    /**
     * @param $id
     * @return mixed
     */
    function setId($id)
    {
        return $this->getFieldValue('id', $id);
    }

    /**
     * @return mixed
     */
    function getProductId()
    {
        return $this->getFieldValue('productId');
    }

    /**
     * @param $productId
     * @return mixed
     */
    function setProductId($productId)
    {
        return $this->setFieldValue('productId', $productId);
    }

    /**
     * @return mixed
     */
    function getJobTypeId()
    {
        return $this->getFieldValue('job_type_id');
    }

    /**
     * @param $jobTypeId
     * @return mixed
     */
    function setJobTypeId($jobTypeId)
    {
        return $this->setFieldValue('job_type_id', $jobTypeId);
    }

    /**
     * @return mixed
     */
    function getProjectId()
    {
        return $this->getFieldValue('project_id');
    }

    /**
     * @param $projectId
     * @return mixed
     */
    function setProjectId($projectId)
    {
        return $this->setFieldValue('project_id', $projectId);
    }

    function getProject()
    {
        return new Project($this->getProjectId());
    }

    function getJobType()
    {
        return new JobType($this->getJobTypeId());
    }

    /**
     * @param $jobTypeId
     * @param $projectId
     * @return BillysJobType
     */
    static function getBillysJobType($jobTypeId, $projectId)
    {

        $result = DB::execute('SELECT id FROM ' . TABLE_PREFIX . 'billys_job_types WHERE job_type_id=? AND project_id=?', $jobTypeId, $projectId);

        if (!empty($result)) {
            $row = $result->getRowAt(0);
            return new BillysJobType($row['id']);
        } else {
            return new BillysJobType(null, $jobTypeId, $projectId);
        }
    }

    public static function count()
    {
        $count = DB::execute('SELECT COUNT(id) as count FROM ' . TABLE_PREFIX . 'billys_job_types');
        $count = $count->getRowAt(0);
        return $count['count'];
    }


}