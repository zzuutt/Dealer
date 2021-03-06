<?php

namespace Dealer\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use Dealer\Model\Dealer as ChildDealer;
use Dealer\Model\DealerQuery as ChildDealerQuery;
use Dealer\Model\DealerShedules as ChildDealerShedules;
use Dealer\Model\DealerShedulesQuery as ChildDealerShedulesQuery;
use Dealer\Model\DealerShedulesVersion as ChildDealerShedulesVersion;
use Dealer\Model\DealerShedulesVersionQuery as ChildDealerShedulesVersionQuery;
use Dealer\Model\DealerVersionQuery as ChildDealerVersionQuery;
use Dealer\Model\Map\DealerShedulesTableMap;
use Dealer\Model\Map\DealerShedulesVersionTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;

abstract class DealerShedules implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Dealer\\Model\\Map\\DealerShedulesTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the dealer_id field.
     * @var        int
     */
    protected $dealer_id;

    /**
     * The value for the day field.
     * @var        int
     */
    protected $day;

    /**
     * The value for the begin field.
     * @var        string
     */
    protected $begin;

    /**
     * The value for the end field.
     * @var        string
     */
    protected $end;

    /**
     * The value for the closed field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $closed;

    /**
     * The value for the period_begin field.
     * @var        string
     */
    protected $period_begin;

    /**
     * The value for the period_end field.
     * @var        string
     */
    protected $period_end;

    /**
     * The value for the created_at field.
     * @var        string
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     * @var        string
     */
    protected $updated_at;

    /**
     * The value for the version field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $version;

    /**
     * The value for the version_created_at field.
     * @var        string
     */
    protected $version_created_at;

    /**
     * The value for the version_created_by field.
     * @var        string
     */
    protected $version_created_by;

    /**
     * @var        Dealer
     */
    protected $aDealer;

    /**
     * @var        ObjectCollection|ChildDealerShedulesVersion[] Collection to store aggregation of ChildDealerShedulesVersion objects.
     */
    protected $collDealerShedulesVersions;
    protected $collDealerShedulesVersionsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    // versionable behavior


    /**
     * @var bool
     */
    protected $enforceVersion = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $dealerShedulesVersionsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->closed = false;
        $this->version = 0;
    }

    /**
     * Initializes internal state of Dealer\Model\Base\DealerShedules object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (Boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (Boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>DealerShedules</code> instance.  If
     * <code>obj</code> is an instance of <code>DealerShedules</code>, delegates to
     * <code>equals(DealerShedules)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return DealerShedules The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return DealerShedules The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);

        return $this;
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [dealer_id] column value.
     *
     * @return   int
     */
    public function getDealerId()
    {

        return $this->dealer_id;
    }

    /**
     * Get the [day] column value.
     *
     * @return   int
     */
    public function getDay()
    {

        return $this->day;
    }

    /**
     * Get the [optionally formatted] temporal [begin] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getBegin($format = NULL)
    {
        if ($format === null) {
            return $this->begin;
        } else {
            return $this->begin instanceof \DateTime ? $this->begin->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [end] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getEnd($format = NULL)
    {
        if ($format === null) {
            return $this->end;
        } else {
            return $this->end instanceof \DateTime ? $this->end->format($format) : null;
        }
    }

    /**
     * Get the [closed] column value.
     *
     * @return   boolean
     */
    public function getClosed()
    {

        return $this->closed;
    }

    /**
     * Get the [optionally formatted] temporal [period_begin] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getPeriodBegin($format = NULL)
    {
        if ($format === null) {
            return $this->period_begin;
        } else {
            return $this->period_begin instanceof \DateTime ? $this->period_begin->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [period_end] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getPeriodEnd($format = NULL)
    {
        if ($format === null) {
            return $this->period_end;
        } else {
            return $this->period_end instanceof \DateTime ? $this->period_end->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at instanceof \DateTime ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at instanceof \DateTime ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Get the [version] column value.
     *
     * @return   int
     */
    public function getVersion()
    {

        return $this->version;
    }

    /**
     * Get the [optionally formatted] temporal [version_created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getVersionCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->version_created_at;
        } else {
            return $this->version_created_at instanceof \DateTime ? $this->version_created_at->format($format) : null;
        }
    }

    /**
     * Get the [version_created_by] column value.
     *
     * @return   string
     */
    public function getVersionCreatedBy()
    {

        return $this->version_created_by;
    }

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[DealerShedulesTableMap::ID] = true;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [dealer_id] column.
     *
     * @param      int $v new value
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setDealerId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->dealer_id !== $v) {
            $this->dealer_id = $v;
            $this->modifiedColumns[DealerShedulesTableMap::DEALER_ID] = true;
        }

        if ($this->aDealer !== null && $this->aDealer->getId() !== $v) {
            $this->aDealer = null;
        }


        return $this;
    } // setDealerId()

    /**
     * Set the value of [day] column.
     *
     * @param      int $v new value
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setDay($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->day !== $v) {
            $this->day = $v;
            $this->modifiedColumns[DealerShedulesTableMap::DAY] = true;
        }


        return $this;
    } // setDay()

    /**
     * Sets the value of [begin] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setBegin($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->begin !== null || $dt !== null) {
            if ($dt !== $this->begin) {
                $this->begin = $dt;
                $this->modifiedColumns[DealerShedulesTableMap::BEGIN] = true;
            }
        } // if either are not null


        return $this;
    } // setBegin()

    /**
     * Sets the value of [end] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setEnd($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->end !== null || $dt !== null) {
            if ($dt !== $this->end) {
                $this->end = $dt;
                $this->modifiedColumns[DealerShedulesTableMap::END] = true;
            }
        } // if either are not null


        return $this;
    } // setEnd()

    /**
     * Sets the value of the [closed] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param      boolean|integer|string $v The new value
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setClosed($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->closed !== $v) {
            $this->closed = $v;
            $this->modifiedColumns[DealerShedulesTableMap::CLOSED] = true;
        }


        return $this;
    } // setClosed()

    /**
     * Sets the value of [period_begin] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setPeriodBegin($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->period_begin !== null || $dt !== null) {
            if ($dt !== $this->period_begin) {
                $this->period_begin = $dt;
                $this->modifiedColumns[DealerShedulesTableMap::PERIOD_BEGIN] = true;
            }
        } // if either are not null


        return $this;
    } // setPeriodBegin()

    /**
     * Sets the value of [period_end] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setPeriodEnd($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->period_end !== null || $dt !== null) {
            if ($dt !== $this->period_end) {
                $this->period_end = $dt;
                $this->modifiedColumns[DealerShedulesTableMap::PERIOD_END] = true;
            }
        } // if either are not null


        return $this;
    } // setPeriodEnd()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($dt !== $this->created_at) {
                $this->created_at = $dt;
                $this->modifiedColumns[DealerShedulesTableMap::CREATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($dt !== $this->updated_at) {
                $this->updated_at = $dt;
                $this->modifiedColumns[DealerShedulesTableMap::UPDATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setUpdatedAt()

    /**
     * Set the value of [version] column.
     *
     * @param      int $v new value
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setVersion($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->version !== $v) {
            $this->version = $v;
            $this->modifiedColumns[DealerShedulesTableMap::VERSION] = true;
        }


        return $this;
    } // setVersion()

    /**
     * Sets the value of [version_created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setVersionCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->version_created_at !== null || $dt !== null) {
            if ($dt !== $this->version_created_at) {
                $this->version_created_at = $dt;
                $this->modifiedColumns[DealerShedulesTableMap::VERSION_CREATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setVersionCreatedAt()

    /**
     * Set the value of [version_created_by] column.
     *
     * @param      string $v new value
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function setVersionCreatedBy($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->version_created_by !== $v) {
            $this->version_created_by = $v;
            $this->modifiedColumns[DealerShedulesTableMap::VERSION_CREATED_BY] = true;
        }


        return $this;
    } // setVersionCreatedBy()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->closed !== false) {
                return false;
            }

            if ($this->version !== 0) {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : DealerShedulesTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : DealerShedulesTableMap::translateFieldName('DealerId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->dealer_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : DealerShedulesTableMap::translateFieldName('Day', TableMap::TYPE_PHPNAME, $indexType)];
            $this->day = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : DealerShedulesTableMap::translateFieldName('Begin', TableMap::TYPE_PHPNAME, $indexType)];
            $this->begin = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : DealerShedulesTableMap::translateFieldName('End', TableMap::TYPE_PHPNAME, $indexType)];
            $this->end = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : DealerShedulesTableMap::translateFieldName('Closed', TableMap::TYPE_PHPNAME, $indexType)];
            $this->closed = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : DealerShedulesTableMap::translateFieldName('PeriodBegin', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00') {
                $col = null;
            }
            $this->period_begin = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : DealerShedulesTableMap::translateFieldName('PeriodEnd', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00') {
                $col = null;
            }
            $this->period_end = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : DealerShedulesTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : DealerShedulesTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 10 + $startcol : DealerShedulesTableMap::translateFieldName('Version', TableMap::TYPE_PHPNAME, $indexType)];
            $this->version = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 11 + $startcol : DealerShedulesTableMap::translateFieldName('VersionCreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->version_created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 12 + $startcol : DealerShedulesTableMap::translateFieldName('VersionCreatedBy', TableMap::TYPE_PHPNAME, $indexType)];
            $this->version_created_by = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 13; // 13 = DealerShedulesTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \Dealer\Model\DealerShedules object", 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aDealer !== null && $this->dealer_id !== $this->aDealer->getId()) {
            $this->aDealer = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(DealerShedulesTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildDealerShedulesQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aDealer = null;
            $this->collDealerShedulesVersions = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see DealerShedules::setDeleted()
     * @see DealerShedules::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(DealerShedulesTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildDealerShedulesQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(DealerShedulesTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // versionable behavior
            if ($this->isVersioningNecessary()) {
                $this->setVersion($this->isNew() ? 1 : $this->getLastVersionNumber($con) + 1);
                if (!$this->isColumnModified(DealerShedulesTableMap::VERSION_CREATED_AT)) {
                    $this->setVersionCreatedAt(time());
                }
                $createVersion = true; // for postSave hook
            }
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                if (!$this->isColumnModified(DealerShedulesTableMap::CREATED_AT)) {
                    $this->setCreatedAt(time());
                }
                if (!$this->isColumnModified(DealerShedulesTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(DealerShedulesTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                // versionable behavior
                if (isset($createVersion)) {
                    $this->addVersion($con);
                }
                DealerShedulesTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aDealer !== null) {
                if ($this->aDealer->isModified() || $this->aDealer->isNew()) {
                    $affectedRows += $this->aDealer->save($con);
                }
                $this->setDealer($this->aDealer);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->dealerShedulesVersionsScheduledForDeletion !== null) {
                if (!$this->dealerShedulesVersionsScheduledForDeletion->isEmpty()) {
                    \Dealer\Model\DealerShedulesVersionQuery::create()
                        ->filterByPrimaryKeys($this->dealerShedulesVersionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->dealerShedulesVersionsScheduledForDeletion = null;
                }
            }

                if ($this->collDealerShedulesVersions !== null) {
            foreach ($this->collDealerShedulesVersions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[DealerShedulesTableMap::ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . DealerShedulesTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(DealerShedulesTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::DEALER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'DEALER_ID';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::DAY)) {
            $modifiedColumns[':p' . $index++]  = 'DAY';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::BEGIN)) {
            $modifiedColumns[':p' . $index++]  = 'BEGIN';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::END)) {
            $modifiedColumns[':p' . $index++]  = 'END';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::CLOSED)) {
            $modifiedColumns[':p' . $index++]  = 'CLOSED';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::PERIOD_BEGIN)) {
            $modifiedColumns[':p' . $index++]  = 'PERIOD_BEGIN';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::PERIOD_END)) {
            $modifiedColumns[':p' . $index++]  = 'PERIOD_END';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'CREATED_AT';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'UPDATED_AT';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::VERSION)) {
            $modifiedColumns[':p' . $index++]  = 'VERSION';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::VERSION_CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'VERSION_CREATED_AT';
        }
        if ($this->isColumnModified(DealerShedulesTableMap::VERSION_CREATED_BY)) {
            $modifiedColumns[':p' . $index++]  = 'VERSION_CREATED_BY';
        }

        $sql = sprintf(
            'INSERT INTO dealer_shedules (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'DEALER_ID':
                        $stmt->bindValue($identifier, $this->dealer_id, PDO::PARAM_INT);
                        break;
                    case 'DAY':
                        $stmt->bindValue($identifier, $this->day, PDO::PARAM_INT);
                        break;
                    case 'BEGIN':
                        $stmt->bindValue($identifier, $this->begin ? $this->begin->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'END':
                        $stmt->bindValue($identifier, $this->end ? $this->end->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'CLOSED':
                        $stmt->bindValue($identifier, (int) $this->closed, PDO::PARAM_INT);
                        break;
                    case 'PERIOD_BEGIN':
                        $stmt->bindValue($identifier, $this->period_begin ? $this->period_begin->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'PERIOD_END':
                        $stmt->bindValue($identifier, $this->period_end ? $this->period_end->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'CREATED_AT':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'UPDATED_AT':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'VERSION':
                        $stmt->bindValue($identifier, $this->version, PDO::PARAM_INT);
                        break;
                    case 'VERSION_CREATED_AT':
                        $stmt->bindValue($identifier, $this->version_created_at ? $this->version_created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'VERSION_CREATED_BY':
                        $stmt->bindValue($identifier, $this->version_created_by, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = DealerShedulesTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getDealerId();
                break;
            case 2:
                return $this->getDay();
                break;
            case 3:
                return $this->getBegin();
                break;
            case 4:
                return $this->getEnd();
                break;
            case 5:
                return $this->getClosed();
                break;
            case 6:
                return $this->getPeriodBegin();
                break;
            case 7:
                return $this->getPeriodEnd();
                break;
            case 8:
                return $this->getCreatedAt();
                break;
            case 9:
                return $this->getUpdatedAt();
                break;
            case 10:
                return $this->getVersion();
                break;
            case 11:
                return $this->getVersionCreatedAt();
                break;
            case 12:
                return $this->getVersionCreatedBy();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['DealerShedules'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['DealerShedules'][$this->getPrimaryKey()] = true;
        $keys = DealerShedulesTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getDealerId(),
            $keys[2] => $this->getDay(),
            $keys[3] => $this->getBegin(),
            $keys[4] => $this->getEnd(),
            $keys[5] => $this->getClosed(),
            $keys[6] => $this->getPeriodBegin(),
            $keys[7] => $this->getPeriodEnd(),
            $keys[8] => $this->getCreatedAt(),
            $keys[9] => $this->getUpdatedAt(),
            $keys[10] => $this->getVersion(),
            $keys[11] => $this->getVersionCreatedAt(),
            $keys[12] => $this->getVersionCreatedBy(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aDealer) {
                $result['Dealer'] = $this->aDealer->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collDealerShedulesVersions) {
                $result['DealerShedulesVersions'] = $this->collDealerShedulesVersions->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = DealerShedulesTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setDealerId($value);
                break;
            case 2:
                $this->setDay($value);
                break;
            case 3:
                $this->setBegin($value);
                break;
            case 4:
                $this->setEnd($value);
                break;
            case 5:
                $this->setClosed($value);
                break;
            case 6:
                $this->setPeriodBegin($value);
                break;
            case 7:
                $this->setPeriodEnd($value);
                break;
            case 8:
                $this->setCreatedAt($value);
                break;
            case 9:
                $this->setUpdatedAt($value);
                break;
            case 10:
                $this->setVersion($value);
                break;
            case 11:
                $this->setVersionCreatedAt($value);
                break;
            case 12:
                $this->setVersionCreatedBy($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = DealerShedulesTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setDealerId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setDay($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setBegin($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setEnd($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setClosed($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setPeriodBegin($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setPeriodEnd($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setCreatedAt($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setUpdatedAt($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setVersion($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setVersionCreatedAt($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setVersionCreatedBy($arr[$keys[12]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(DealerShedulesTableMap::DATABASE_NAME);

        if ($this->isColumnModified(DealerShedulesTableMap::ID)) $criteria->add(DealerShedulesTableMap::ID, $this->id);
        if ($this->isColumnModified(DealerShedulesTableMap::DEALER_ID)) $criteria->add(DealerShedulesTableMap::DEALER_ID, $this->dealer_id);
        if ($this->isColumnModified(DealerShedulesTableMap::DAY)) $criteria->add(DealerShedulesTableMap::DAY, $this->day);
        if ($this->isColumnModified(DealerShedulesTableMap::BEGIN)) $criteria->add(DealerShedulesTableMap::BEGIN, $this->begin);
        if ($this->isColumnModified(DealerShedulesTableMap::END)) $criteria->add(DealerShedulesTableMap::END, $this->end);
        if ($this->isColumnModified(DealerShedulesTableMap::CLOSED)) $criteria->add(DealerShedulesTableMap::CLOSED, $this->closed);
        if ($this->isColumnModified(DealerShedulesTableMap::PERIOD_BEGIN)) $criteria->add(DealerShedulesTableMap::PERIOD_BEGIN, $this->period_begin);
        if ($this->isColumnModified(DealerShedulesTableMap::PERIOD_END)) $criteria->add(DealerShedulesTableMap::PERIOD_END, $this->period_end);
        if ($this->isColumnModified(DealerShedulesTableMap::CREATED_AT)) $criteria->add(DealerShedulesTableMap::CREATED_AT, $this->created_at);
        if ($this->isColumnModified(DealerShedulesTableMap::UPDATED_AT)) $criteria->add(DealerShedulesTableMap::UPDATED_AT, $this->updated_at);
        if ($this->isColumnModified(DealerShedulesTableMap::VERSION)) $criteria->add(DealerShedulesTableMap::VERSION, $this->version);
        if ($this->isColumnModified(DealerShedulesTableMap::VERSION_CREATED_AT)) $criteria->add(DealerShedulesTableMap::VERSION_CREATED_AT, $this->version_created_at);
        if ($this->isColumnModified(DealerShedulesTableMap::VERSION_CREATED_BY)) $criteria->add(DealerShedulesTableMap::VERSION_CREATED_BY, $this->version_created_by);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(DealerShedulesTableMap::DATABASE_NAME);
        $criteria->add(DealerShedulesTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \Dealer\Model\DealerShedules (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setDealerId($this->getDealerId());
        $copyObj->setDay($this->getDay());
        $copyObj->setBegin($this->getBegin());
        $copyObj->setEnd($this->getEnd());
        $copyObj->setClosed($this->getClosed());
        $copyObj->setPeriodBegin($this->getPeriodBegin());
        $copyObj->setPeriodEnd($this->getPeriodEnd());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());
        $copyObj->setVersion($this->getVersion());
        $copyObj->setVersionCreatedAt($this->getVersionCreatedAt());
        $copyObj->setVersionCreatedBy($this->getVersionCreatedBy());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getDealerShedulesVersions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addDealerShedulesVersion($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return                 \Dealer\Model\DealerShedules Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildDealer object.
     *
     * @param                  ChildDealer $v
     * @return                 \Dealer\Model\DealerShedules The current object (for fluent API support)
     * @throws PropelException
     */
    public function setDealer(ChildDealer $v = null)
    {
        if ($v === null) {
            $this->setDealerId(NULL);
        } else {
            $this->setDealerId($v->getId());
        }

        $this->aDealer = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildDealer object, it will not be re-added.
        if ($v !== null) {
            $v->addDealerShedules($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildDealer object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildDealer The associated ChildDealer object.
     * @throws PropelException
     */
    public function getDealer(ConnectionInterface $con = null)
    {
        if ($this->aDealer === null && ($this->dealer_id !== null)) {
            $this->aDealer = ChildDealerQuery::create()->findPk($this->dealer_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aDealer->addDealerSheduless($this);
             */
        }

        return $this->aDealer;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('DealerShedulesVersion' == $relationName) {
            return $this->initDealerShedulesVersions();
        }
    }

    /**
     * Clears out the collDealerShedulesVersions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addDealerShedulesVersions()
     */
    public function clearDealerShedulesVersions()
    {
        $this->collDealerShedulesVersions = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collDealerShedulesVersions collection loaded partially.
     */
    public function resetPartialDealerShedulesVersions($v = true)
    {
        $this->collDealerShedulesVersionsPartial = $v;
    }

    /**
     * Initializes the collDealerShedulesVersions collection.
     *
     * By default this just sets the collDealerShedulesVersions collection to an empty array (like clearcollDealerShedulesVersions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initDealerShedulesVersions($overrideExisting = true)
    {
        if (null !== $this->collDealerShedulesVersions && !$overrideExisting) {
            return;
        }
        $this->collDealerShedulesVersions = new ObjectCollection();
        $this->collDealerShedulesVersions->setModel('\Dealer\Model\DealerShedulesVersion');
    }

    /**
     * Gets an array of ChildDealerShedulesVersion objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildDealerShedules is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildDealerShedulesVersion[] List of ChildDealerShedulesVersion objects
     * @throws PropelException
     */
    public function getDealerShedulesVersions($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collDealerShedulesVersionsPartial && !$this->isNew();
        if (null === $this->collDealerShedulesVersions || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collDealerShedulesVersions) {
                // return empty collection
                $this->initDealerShedulesVersions();
            } else {
                $collDealerShedulesVersions = ChildDealerShedulesVersionQuery::create(null, $criteria)
                    ->filterByDealerShedules($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collDealerShedulesVersionsPartial && count($collDealerShedulesVersions)) {
                        $this->initDealerShedulesVersions(false);

                        foreach ($collDealerShedulesVersions as $obj) {
                            if (false == $this->collDealerShedulesVersions->contains($obj)) {
                                $this->collDealerShedulesVersions->append($obj);
                            }
                        }

                        $this->collDealerShedulesVersionsPartial = true;
                    }

                    reset($collDealerShedulesVersions);

                    return $collDealerShedulesVersions;
                }

                if ($partial && $this->collDealerShedulesVersions) {
                    foreach ($this->collDealerShedulesVersions as $obj) {
                        if ($obj->isNew()) {
                            $collDealerShedulesVersions[] = $obj;
                        }
                    }
                }

                $this->collDealerShedulesVersions = $collDealerShedulesVersions;
                $this->collDealerShedulesVersionsPartial = false;
            }
        }

        return $this->collDealerShedulesVersions;
    }

    /**
     * Sets a collection of DealerShedulesVersion objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $dealerShedulesVersions A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildDealerShedules The current object (for fluent API support)
     */
    public function setDealerShedulesVersions(Collection $dealerShedulesVersions, ConnectionInterface $con = null)
    {
        $dealerShedulesVersionsToDelete = $this->getDealerShedulesVersions(new Criteria(), $con)->diff($dealerShedulesVersions);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->dealerShedulesVersionsScheduledForDeletion = clone $dealerShedulesVersionsToDelete;

        foreach ($dealerShedulesVersionsToDelete as $dealerShedulesVersionRemoved) {
            $dealerShedulesVersionRemoved->setDealerShedules(null);
        }

        $this->collDealerShedulesVersions = null;
        foreach ($dealerShedulesVersions as $dealerShedulesVersion) {
            $this->addDealerShedulesVersion($dealerShedulesVersion);
        }

        $this->collDealerShedulesVersions = $dealerShedulesVersions;
        $this->collDealerShedulesVersionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related DealerShedulesVersion objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related DealerShedulesVersion objects.
     * @throws PropelException
     */
    public function countDealerShedulesVersions(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collDealerShedulesVersionsPartial && !$this->isNew();
        if (null === $this->collDealerShedulesVersions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collDealerShedulesVersions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getDealerShedulesVersions());
            }

            $query = ChildDealerShedulesVersionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByDealerShedules($this)
                ->count($con);
        }

        return count($this->collDealerShedulesVersions);
    }

    /**
     * Method called to associate a ChildDealerShedulesVersion object to this object
     * through the ChildDealerShedulesVersion foreign key attribute.
     *
     * @param    ChildDealerShedulesVersion $l ChildDealerShedulesVersion
     * @return   \Dealer\Model\DealerShedules The current object (for fluent API support)
     */
    public function addDealerShedulesVersion(ChildDealerShedulesVersion $l)
    {
        if ($this->collDealerShedulesVersions === null) {
            $this->initDealerShedulesVersions();
            $this->collDealerShedulesVersionsPartial = true;
        }

        if (!in_array($l, $this->collDealerShedulesVersions->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddDealerShedulesVersion($l);
        }

        return $this;
    }

    /**
     * @param DealerShedulesVersion $dealerShedulesVersion The dealerShedulesVersion object to add.
     */
    protected function doAddDealerShedulesVersion($dealerShedulesVersion)
    {
        $this->collDealerShedulesVersions[]= $dealerShedulesVersion;
        $dealerShedulesVersion->setDealerShedules($this);
    }

    /**
     * @param  DealerShedulesVersion $dealerShedulesVersion The dealerShedulesVersion object to remove.
     * @return ChildDealerShedules The current object (for fluent API support)
     */
    public function removeDealerShedulesVersion($dealerShedulesVersion)
    {
        if ($this->getDealerShedulesVersions()->contains($dealerShedulesVersion)) {
            $this->collDealerShedulesVersions->remove($this->collDealerShedulesVersions->search($dealerShedulesVersion));
            if (null === $this->dealerShedulesVersionsScheduledForDeletion) {
                $this->dealerShedulesVersionsScheduledForDeletion = clone $this->collDealerShedulesVersions;
                $this->dealerShedulesVersionsScheduledForDeletion->clear();
            }
            $this->dealerShedulesVersionsScheduledForDeletion[]= clone $dealerShedulesVersion;
            $dealerShedulesVersion->setDealerShedules(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->dealer_id = null;
        $this->day = null;
        $this->begin = null;
        $this->end = null;
        $this->closed = null;
        $this->period_begin = null;
        $this->period_end = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->version = null;
        $this->version_created_at = null;
        $this->version_created_by = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collDealerShedulesVersions) {
                foreach ($this->collDealerShedulesVersions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collDealerShedulesVersions = null;
        $this->aDealer = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(DealerShedulesTableMap::DEFAULT_STRING_FORMAT);
    }

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     ChildDealerShedules The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[DealerShedulesTableMap::UPDATED_AT] = true;

        return $this;
    }

    // versionable behavior

    /**
     * Enforce a new Version of this object upon next save.
     *
     * @return \Dealer\Model\DealerShedules
     */
    public function enforceVersioning()
    {
        $this->enforceVersion = true;

        return $this;
    }

    /**
     * Checks whether the current state must be recorded as a version
     *
     * @return  boolean
     */
    public function isVersioningNecessary($con = null)
    {
        if ($this->alreadyInSave) {
            return false;
        }

        if ($this->enforceVersion) {
            return true;
        }

        if (ChildDealerShedulesQuery::isVersioningEnabled() && ($this->isNew() || $this->isModified()) || $this->isDeleted()) {
            return true;
        }
        if (null !== ($object = $this->getDealer($con)) && $object->isVersioningNecessary($con)) {
            return true;
        }


        return false;
    }

    /**
     * Creates a version of the current object and saves it.
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ChildDealerShedulesVersion A version object
     */
    public function addVersion($con = null)
    {
        $this->enforceVersion = false;

        $version = new ChildDealerShedulesVersion();
        $version->setId($this->getId());
        $version->setDealerId($this->getDealerId());
        $version->setDay($this->getDay());
        $version->setBegin($this->getBegin());
        $version->setEnd($this->getEnd());
        $version->setClosed($this->getClosed());
        $version->setPeriodBegin($this->getPeriodBegin());
        $version->setPeriodEnd($this->getPeriodEnd());
        $version->setCreatedAt($this->getCreatedAt());
        $version->setUpdatedAt($this->getUpdatedAt());
        $version->setVersion($this->getVersion());
        $version->setVersionCreatedAt($this->getVersionCreatedAt());
        $version->setVersionCreatedBy($this->getVersionCreatedBy());
        $version->setDealerShedules($this);
        if (($related = $this->getDealer($con)) && $related->getVersion()) {
            $version->setDealerIdVersion($related->getVersion());
        }
        $version->save($con);

        return $version;
    }

    /**
     * Sets the properties of the current object to the value they had at a specific version
     *
     * @param   integer $versionNumber The version number to read
     * @param   ConnectionInterface $con The connection to use
     *
     * @return  ChildDealerShedules The current object (for fluent API support)
     */
    public function toVersion($versionNumber, $con = null)
    {
        $version = $this->getOneVersion($versionNumber, $con);
        if (!$version) {
            throw new PropelException(sprintf('No ChildDealerShedules object found with version %d', $version));
        }
        $this->populateFromVersion($version, $con);

        return $this;
    }

    /**
     * Sets the properties of the current object to the value they had at a specific version
     *
     * @param ChildDealerShedulesVersion $version The version object to use
     * @param ConnectionInterface   $con the connection to use
     * @param array                 $loadedObjects objects that been loaded in a chain of populateFromVersion calls on referrer or fk objects.
     *
     * @return ChildDealerShedules The current object (for fluent API support)
     */
    public function populateFromVersion($version, $con = null, &$loadedObjects = array())
    {
        $loadedObjects['ChildDealerShedules'][$version->getId()][$version->getVersion()] = $this;
        $this->setId($version->getId());
        $this->setDealerId($version->getDealerId());
        $this->setDay($version->getDay());
        $this->setBegin($version->getBegin());
        $this->setEnd($version->getEnd());
        $this->setClosed($version->getClosed());
        $this->setPeriodBegin($version->getPeriodBegin());
        $this->setPeriodEnd($version->getPeriodEnd());
        $this->setCreatedAt($version->getCreatedAt());
        $this->setUpdatedAt($version->getUpdatedAt());
        $this->setVersion($version->getVersion());
        $this->setVersionCreatedAt($version->getVersionCreatedAt());
        $this->setVersionCreatedBy($version->getVersionCreatedBy());
        if ($fkValue = $version->getDealerId()) {
            if (isset($loadedObjects['ChildDealer']) && isset($loadedObjects['ChildDealer'][$fkValue]) && isset($loadedObjects['ChildDealer'][$fkValue][$version->getDealerIdVersion()])) {
                $related = $loadedObjects['ChildDealer'][$fkValue][$version->getDealerIdVersion()];
            } else {
                $related = new ChildDealer();
                $relatedVersion = ChildDealerVersionQuery::create()
                    ->filterById($fkValue)
                    ->filterByVersion($version->getDealerIdVersion())
                    ->findOne($con);
                $related->populateFromVersion($relatedVersion, $con, $loadedObjects);
                $related->setNew(false);
            }
            $this->setDealer($related);
        }

        return $this;
    }

    /**
     * Gets the latest persisted version number for the current object
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  integer
     */
    public function getLastVersionNumber($con = null)
    {
        $v = ChildDealerShedulesVersionQuery::create()
            ->filterByDealerShedules($this)
            ->orderByVersion('desc')
            ->findOne($con);
        if (!$v) {
            return 0;
        }

        return $v->getVersion();
    }

    /**
     * Checks whether the current object is the latest one
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  Boolean
     */
    public function isLastVersion($con = null)
    {
        return $this->getLastVersionNumber($con) == $this->getVersion();
    }

    /**
     * Retrieves a version object for this entity and a version number
     *
     * @param   integer $versionNumber The version number to read
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ChildDealerShedulesVersion A version object
     */
    public function getOneVersion($versionNumber, $con = null)
    {
        return ChildDealerShedulesVersionQuery::create()
            ->filterByDealerShedules($this)
            ->filterByVersion($versionNumber)
            ->findOne($con);
    }

    /**
     * Gets all the versions of this object, in incremental order
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ObjectCollection A list of ChildDealerShedulesVersion objects
     */
    public function getAllVersions($con = null)
    {
        $criteria = new Criteria();
        $criteria->addAscendingOrderByColumn(DealerShedulesVersionTableMap::VERSION);

        return $this->getDealerShedulesVersions($criteria, $con);
    }

    /**
     * Compares the current object with another of its version.
     * <code>
     * print_r($book->compareVersion(1));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   integer             $versionNumber
     * @param   string              $keys Main key used for the result diff (versions|columns)
     * @param   ConnectionInterface $con the connection to use
     * @param   array               $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    public function compareVersion($versionNumber, $keys = 'columns', $con = null, $ignoredColumns = array())
    {
        $fromVersion = $this->toArray();
        $toVersion = $this->getOneVersion($versionNumber, $con)->toArray();

        return $this->computeDiff($fromVersion, $toVersion, $keys, $ignoredColumns);
    }

    /**
     * Compares two versions of the current object.
     * <code>
     * print_r($book->compareVersions(1, 2));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   integer             $fromVersionNumber
     * @param   integer             $toVersionNumber
     * @param   string              $keys Main key used for the result diff (versions|columns)
     * @param   ConnectionInterface $con the connection to use
     * @param   array               $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    public function compareVersions($fromVersionNumber, $toVersionNumber, $keys = 'columns', $con = null, $ignoredColumns = array())
    {
        $fromVersion = $this->getOneVersion($fromVersionNumber, $con)->toArray();
        $toVersion = $this->getOneVersion($toVersionNumber, $con)->toArray();

        return $this->computeDiff($fromVersion, $toVersion, $keys, $ignoredColumns);
    }

    /**
     * Computes the diff between two versions.
     * <code>
     * print_r($book->computeDiff(1, 2));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   array     $fromVersion     An array representing the original version.
     * @param   array     $toVersion       An array representing the destination version.
     * @param   string    $keys            Main key used for the result diff (versions|columns).
     * @param   array     $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    protected function computeDiff($fromVersion, $toVersion, $keys = 'columns', $ignoredColumns = array())
    {
        $fromVersionNumber = $fromVersion['Version'];
        $toVersionNumber = $toVersion['Version'];
        $ignoredColumns = array_merge(array(
            'Version',
            'VersionCreatedAt',
            'VersionCreatedBy',
        ), $ignoredColumns);
        $diff = array();
        foreach ($fromVersion as $key => $value) {
            if (in_array($key, $ignoredColumns)) {
                continue;
            }
            if ($toVersion[$key] != $value) {
                switch ($keys) {
                    case 'versions':
                        $diff[$fromVersionNumber][$key] = $value;
                        $diff[$toVersionNumber][$key] = $toVersion[$key];
                        break;
                    default:
                        $diff[$key] = array(
                            $fromVersionNumber => $value,
                            $toVersionNumber => $toVersion[$key],
                        );
                        break;
                }
            }
        }

        return $diff;
    }
    /**
     * retrieve the last $number versions.
     *
     * @param Integer $number the number of record to return.
     * @return PropelCollection|array \Dealer\Model\DealerShedulesVersion[] List of \Dealer\Model\DealerShedulesVersion objects
     */
    public function getLastVersions($number = 10, $criteria = null, $con = null)
    {
        $criteria = ChildDealerShedulesVersionQuery::create(null, $criteria);
        $criteria->addDescendingOrderByColumn(DealerShedulesVersionTableMap::VERSION);
        $criteria->limit($number);

        return $this->getDealerShedulesVersions($criteria, $con);
    }
    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
