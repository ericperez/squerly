<?php
    /**
    * @name        D3Linq | Linq In Php
    * @version     1.6.0
    * @author      Tufan Baris YILDIRIM
    * @link        htpp://www.tufyta.com
    * @since       20.10.2009
    *
    * v1.6.0
    * =======
    * - d3grid Integrated.
    * - i've re-written some complex code blocks.
    *
    * v1.5.5
    * ======
    * - xml_string() added for xml string vars.also xml_file can select from xml string var.
    * - tags which have 2 letters (eg. li ul dt dl etc..) parsing bug [fixed]
    * - added preg_quote to LIKE creater function. [fixed]
    * - added a control for integer keys on unsetByArray [fixed]
    * v1.5.4
    * =======
    * - html tag names lowered after crawl
    * - unsetByArray added for unset variables which created by extract();
    * - all rows was returning when first row is matched and other hasn't got same columns (resolved by usetByArray) [fixed]
    * v1.5.3
    * ======
    * - GetContentsFunc public variable added  for change  file_get_contents func as u declared. Ex: curl
    * - Notice Errors resolved.
    * - much moreee stronger html parser (:
    * - dir_file and dir_files functions added for select dirs or files from a dir. ex: select from dir_files(dirname) WHERE  name LIKE '%myfile%'
    * v1.5.2
    * ======
    * - new json_file(  can read serialized data by php or json encoded data
    * - performed about stabilization . unsetted big arrays and unnecessaried used variables
    * - bug about dot obje to an array when child obje key is numeric [fixed]
    *   -- Tools Funcsions Added --
    * -  arrayRebuild
    *    ------------
    * - Can Rebuild an array by a template you given
    * - getArrayName
    *   -------------
    * - return last array name .. or last created virtual arrayname after query.
    * - getResult
    *   -------------
    * - get all result array.
    * v1.5.1
    * ======
    * - text_file(); function added.
    *                  .Ex: select line,text from text_file(file.txt) where line=50 OR text LIKE '%linq%'
    * - html_file(); function added.
    *                  .Ex: select  innertHTML,text FROM html_file(http://tufyta.com).div WHERE class='post_title';
    * - name of selected array by func cannot start with a numeric char [fixed]
    * - "NOT" support added for LIKE claueses.
    * - debug() func added. can selfdebug on error if OnError=='selfdebug';
    * - Token Analyzer and Token errors Added.
    * - CreateWhereClause Func Added for create a boolean returnable php code from WHERE clause you wroten
    * v1.5.0
    * ======
    * - FirstOf() function changed.(used foreach).
    * - Order by bug fixed (invalid column error when column has a space char).
    * - Updatable keys and values (UPDATE array SET value=....).
    * - Array Update errors added.
    * - Func Support Added. (can create and use this func in queries)
    *      . json_file().
    *          - can read json to a global array and select from it
    *      .xml_file().
    *          - can read a xml file (as dataset) to a global array and select from it or a datatable in it.
    * v1.4.0
    * ======
    * - UPDATE statement Added Thanx to Ridvan  http://www.phpclasses.org/browse/author/668797.html
    * - data_seek bug [fixed] reported by Ridvan Karatas at  http://www.phpclasses.org/discuss/package/5893/thread/1/
    * - Order by bug [fixed]
    * - Delete bug (couldnt use after order ) [fixed]
    * v1.3.0
    * ======
    * - Insert Statement Added.
    *      . Can use insert statement like '(key,value) VALUES (...,...) for 1 dimmensional arrays
    *      . and like  (col1,col2,col3) VALUES (..)  for multi dimmensional arrays'
    * v1.2.1
    * =======
    * - Columns can be used as a string in where clause [added]
    * - Last Column Name was wrong. [fixed]
    * - Order was wrong for numerics. [fixed]
    * - Distinct method had an error about implode. [fixed]
    * v1.2.0
    * ======
    * - DISTINCT Support Added.
    * v1.1.0
    * ======
    * - Created Wagons for Easy parse Tokens
    * - ORDER BY Support Added.
    * - Unknown column Error added. Checker for columns are used on ORDER Clauses
    * v1.0.2
    * ======
    * - affected_rows() func added.
    * v1.0.1
    * ======
    * - Delete Statemend added. Can Delete any object from an array ( global )
    * v1.0
    * ======
    * - This Class can be used for select from arrays as a sql query
    * - You;
    * - Can use php codes in Where clauses
    * - Can also use alias for key and value
    * - And can select array in array as  arrayname.elementname
    */
    class D3Linq{

        public    $OnError = 'selfdebug',
        $GetContentsFunc,
        $DebugDeepLimit =0,
        $DebugDeep      =0;

        private   $Index          =0,
        $IndexIn        =0,
        $Result         =array(),
        $Selected       =array(),
        $Dizimiz        =array(),
        #$ResultKeys    =array(),
        #$ResultValues  =array(),
        $isBasic        =False,
        $isComplicated  =False,
        $Tokens         =array(),
        $ErrorMsg       ='',
        $ErrorCode      ='',
        $AffecTedRows   =0,
        $LastSQL        ='',
        /**
        * Token Train
        * @since v1.1.0
        * @var mixed
        */
         $wagons=array(
                'statement'         =>array('wagon' => 'SELECT|DELETE|INSERT|UPDATE','options'=>'','msg'=>'Statement can only be Select,Delete,Insert or Update'),
                'space_1'           =>array('wagon' => '[\s]+','options'=>'?'),
                'unique'            =>array('wagon' => 'DISTINCT','options'=>'?'),
                'columns'           =>array('wagon' => '[a-zA-Z0-9\s,]+|\*|[^\(\s]+','options'=>'?'),
                'space_2'           =>array('wagon' => '[\s]+','options'=>'?'),
                'fromorinto'        =>array('wagon' => 'FROM|INTO','options'=>'','msg'=>'You must use this token either FROM or INTO'),
                'space_3'           =>array('wagon' => '[\s]+','options'=>''),
                'func_name'         =>array('wagon' => 'json_file|xml_file|text_file|html_file|dir_files?|xml_string','options'=>'?'),
                'func_pr1'          =>array('wagon' => '\(','options'=>'?'),
                'arrayname'         =>array('wagon' => '[A-Za-z_0-9\.\[\]\\\:\/%_&\?=\-]+','options'=>'','msg'=>'UnReachable Array'),
                'func_pr2'          =>array('wagon' => '\)','options'=>'?'),
                'func_in'           =>array('wagon' => '\.[A-Za-z_0-9\.\@]+','options'=>'?'),
                'space_4'           =>array('wagon' => '[\s]+','options'=>'*'),

                //Update Clause @since v1.4.0
                'updateClause'      =>array('wagon' => array(
                                                            'set'       =>array('wagon'=>'SET','options'=>''),
                                                            'set_space' =>array('wagon'=>'[\s]+','options'=>''),
                                                            'colandval' =>array('wagon'=>'[A-Za-z0-9_-]+\=.*','options'=>'+'),
                                                            ),
                                                            'options'=>'?'
                                                            ),

                // INSERT Clause  @since v1.3.0
                'insertClause'       =>array('wagon' => array(
                                                        'insertColumns' =>array('wagon'=>'\([a-zA-Z0-9\s,]+\)','options'=>'?'),
                                                        'space_bval'    =>array('wagon'=>'[\s]+','options'=>'?'),
                                                        'values'        =>array('wagon'=>'VALUES','options'=>'?'),
                                                        'space_val'     =>array('wagon'=>'[\s]+','options'=>'?'),
                                                        'colvals'       =>array('wagon'=>'\([^\(]+\)','options'=>'?'),
                                                        ),'options'=>'?'
                                                        ),

                // Where Clause
                'whereClause'       =>array('wagon' => array(
                                                        'where'         =>array('wagon'=>'WHERE','options'=>''),
                                                        'space_where'   =>array('wagon'=>'[\s]+','options'=>''),
                                                        'whereCondition'=>array('wagon'=>'[^;]+','options'=>''),
                                                        ),'options'=>'?'
                                                        ),
                // Order Clause
                'orderClause'       =>array('wagon'=>array(
                                                        'order'             =>array('wagon'=>'ORDER','options'=>''),
                                                        'space_order'       =>array('wagon'=>'[\s]+','options'=>'*'),
                                                        'by'                =>array('wagon'=>'BY','options'=>''),
                                                        'orderCondition'    =>array('wagon'=>'[^;]+','options'=>''),
                                                        ),'options'=>'?'
                                                        )
                ),
                $train;
        #Tokens Index
        const    QR_INDEX                =0,                 // Query Index              #0

        # @since v1.2.0
        UNQ_INDEX               ='unique',
        ST_INDEX                ='statement',       // Statement type Index     #1
        AR_INDEX                ='arrayname',       // Array name Index         #7
        CL_INDEX                ='columns',         // Column Index             #3
        AL_COUNT                =8,                 // Count of matches for a basic query (without WHERE clause)        #8   passive on v1.1.0
        WH_COUNT                =13,                // Count Of matches for a complicated query (with WHERE clauses)    #13  passive on v1.1.0
        CM_WHINDEX              ='whereCondition',  // Where Clause index when is complicated                           #12
        # @since v1.1.0
        OR_INDEX                ='orderCondition',  // Order Clause index       #15
        # @since v1.3.0
        IN_COLS                 ='insertColumns',
        IN_COLVALS              ='colvals',
        # @since v1.4.0
        UP_COLVALS              ='colandval';

        # Errors with Codes
        const    INVALID_SQL             ='010|Invalid SQL',
        MISSING_OPERATORS       ='020|Missing operators',
        MISSING_P               ='021|Missing %1 parenthesis',
        UNEXCEPTED_ERROR        ='030|Unexcepted "<b><i>%1</i></b>"',
        EXCEPTED_BUT            ='031|Excepted "<b><i>%1</i></b>"  but found "<i>%2</i>"',
        NOT_AN_ARRAY            ='040|%1 is not an array',
        NOT_AN_INTEGER          ='041|%1 is not an integer value on %2',
        # @since v1.1.0
        UNKNOWN_COLUMN          ='051|Unknown column "<b>%1</b>"',
        # @since v1.5.0
        UNSUPPORTED_STATEMENT   ='060|Un Supported statement "<b>%1</b>"',
        UNSUPPORTED_FUNCTION    ='061|Un Supported function "<b>%1</b>"',
        ALREADY_HAS_KEY         ='070|The array "<b>%1</b>" already has "<b>%2</b>"',
        FILE_NOT_EXISTS         ='080|"<b>%1</b>" File not exists',
        # @sine v1.5.3
        NOT_A_DIR               ='090|<b>%1</b> is not a directory';




        /**
        * Constructor
        */
        public function __construct()
        {
            $this->Index                =0;
            $this->IndexIn              =0;
            $this->ErrorMsg             ='';
            $this->ErrorCode            ='';
            #$this->ResultKeys          =array();
            #$this->ResultValues        =array();
            $this->Result               =array();
            $this->Selected             =array();
            $this->isBasic              =False;
            $this->isComplicated        =False;
            $this->Tokens               =array();
            $this->AffecTedRows         =0;
            // $this->train=$this->train ? $this->train : $this->CreateTrain(); # passived in v1.3.1
        }


        /**
        * Main Qery Parser
        * @param mixed $SQL it must be valid and basic sql query string
        * 
        */
        public function Query($SQL)
        {
            $this->__construct(); // Reset object. its soo useful on singletion using (:
            $this->LastSQL = $SQL = $this->Escape($SQL);

            #@since v1.1.0
            if(preg_match('/'.$this->CreateTrain($this->wagons['orderClause']['wagon']).'/is',$SQL))
                $this->wagons['orderClause']['options'] = '';
            else
                $this->wagons['orderClause']['options'] = '?'; # @since v1.3.1


            #@since v1.4.0
            if( strtoupper($this->FirstOf(explode(' ',$SQL))) == 'UPDATE' )
            {
                $this->wagons['from']['options'] = '{0}';
                $this->wagons['columns']['options'] = '{0}';
            }
            else {
                $this->wagons['from']['options'] = '';
                $this->wagons['columns']['options'] = '?';
            }

            if(preg_match('/'.$this->CreateTrain($this->wagons['whereClause']['wagon']).'/is',$SQL))
            {
                $this->wagons['whereClause']['options'] = '';

            }
            else
            {

                $this->wagons['whereClause']['options'] = '?'; # @since v1.4.0
            }

            $this->train = $this->CreateTrain();
            preg_match('/'.$this->train.'/is',$SQL,$this->Tokens);

            if(count($this->Tokens) == 0)
                $this->Error(self::INVALID_SQL);

            $this->isComplicated = !empty($this->Tokens[self::CM_WHINDEX]);

            $RpCount = self::CountIn('\)',$this->Tokens[self::QR_INDEX]);
            $LpCount = self::CountIn('\(',$this->Tokens[self::QR_INDEX]);

            if($RpCount!= $LpCount)
            {
                if($RpCount<$LpCount)
                {
                    $this->Error(self::MISSING_P,'right');
                }
                else
                {
                    $this->Error(self::MISSING_P,'left');
                }
            }
            Unset($RpCount,$LpCount);
            #@since v1.5.0
            if ( $this->Tokens['func_name'] )
            {
                $call = $this->Tokens['func_name'];

                if (method_exists($this,$call))
                {
                    $this->$call($this->Tokens['arrayname']);
                }
                else
                {
                    $this->Error(self::UNSUPPORTED_FUNCTION,$call);
                }
            }

            switch (strtoupper($this->Tokens[self::ST_INDEX])){
                case 'SELECT':
                    $this->Select($this->Tokens);
                    break;
                case 'DELETE':
                    $this->Delete($this->Tokens);
                    break;
                case 'INSERT':
                    $this->Insert($this->Tokens);
                    break;
                case 'UPDATE':
                    $this->Update($this->Tokens);
                    break;
                default:
                    $this->Error(self::UNSUPPORTED_STATEMENT,$this->FirstOf(explode(' ',$SQL)));
                    break;
            }

        }
        /**
        * Set the index of current Row
        * @param mixed $Int
        */
        public function data_seek($Int)
        {
            if( is_numeric($Int) )
                $this->Index = $Int;
            else
                $this->Error(self::NOT_AN_INTEGER,array($Int,__FUNCTION__));
        }
        /**
        * Get Unfetched Count
        * @return int
        */
        public  function num_rows()
        {
            return count($this->Result)-$this->Index;
        }


        /**
        * Get Selected Or Deleted Record Counts
        * @since 1.0.2
        */
        public function affected_rows()
        {
            return $this->AffecTedRows;
        }
        /**
        * Fetch Result As D3Field. Return false if no result.
        * @return D3Object or False
        */
        public function fetch_object()
        {
            return   ($Object = $this->fetch_assoc())
            ? (object)$Object
            : false;
        }
        /**
        * Convert the obje name as  arrayname.indname ..to array arrayname[indname]
        * @param mixed $dottedObject
        */
        private function dotObjectToArray($dottedObject)
        {
            if(preg_match('/([A-Z0-9_\.]+)/i',$dottedObject))
            {
                $objs = explode('.',$dottedObject);
                $arrName = $objs[0];
                global $$arrName;
                $globalEleman = $$arrName;

                foreach($objs as  $index=>$name)
                {
                    if($index>0)
                    {
                        $globalEleman = isset($globalEleman[$name])?$globalEleman[$name]:null;
                        $globalName.= '['.$name.']';
                    }
                    else
                        $globalName = $name;
                }

                if(!is_array($globalEleman))
                    $this->Error(self::NOT_AN_ARRAY,$dottedObject);
                else
                    return array('array'=>(array)$globalEleman,'global'=>$globalName,'arrName'=>$arrName);

            }
            else
            {
                $this->Error(self::NOT_AN_ARRAY,$dottedObject);
            }
            return $dottedObject;

        }
        /**
        * My Train Creater Recursive Function by using wagons (:  Cuff Cuuuuffff.... (:
        */
        private function CreateTrain()
        {
            $GetWagons = func_num_args()>0 ? func_get_arg(0) : $this->wagons;
            $Wagon = '';
            foreach($GetWagons AS $wagonName=>$wagonBulk)
            {
                $Wagon .= '(';
                $Wagon .= (isset($wagonBulk['wagon']) && is_array($wagonBulk['wagon'])) ? $this->CreateTrain($wagonBulk['wagon']) : (is_numeric($wagonName) ? null: '?<'.$wagonName.'>').(isset($wagonBulk['wagon']) ? $wagonBulk['wagon'] : null);
                $Wagon .= ')'.$wagonBulk['options'];
            }
            return $Wagon;

        }


        /**
        * public Fetch Result as an Array. return false if not have result
        * @return Array or False
        */
        public function fetch_assoc()
        {
            return isset($this->Result[$this->Index]) ? $this->Result[$this->Index++] :false;
        }
        /**
        * private Fetch Result as an Array. return false if not have result
        * @return Array or False
        */
        private function fetch()
        {
            if(isset($this->Selected[$this->IndexIn]))
            {
                if($this->Tokens[self::CL_INDEX] == '*')
                {
                    $Assoc = $this->Selected[$this->IndexIn];
                    if(is_array($this->Dizimiz))
                        Unset($Assoc['key'],$Assoc['value']);
                }
                else
                {
                    preg_match_all('/(key|value'.(is_array($this->Dizimiz)?'|'.implode('|',array_keys($this->Dizimiz)):null).')((([\s]+)as)?([\s]+)([^,]+))?/i',$this->Tokens[self::CL_INDEX],$ColumnsWithAlias);
                    foreach($ColumnsWithAlias[1] as $colndx=>$colname)
                    {
                        $Assoc[trim(trim($ColumnsWithAlias[6][$colndx]) ? $ColumnsWithAlias[6][$colndx] : $colname)] = $this->Selected[$this->IndexIn][$colname];
                    }

                }
                unset($this->Selected[$this->IndexIn]);
                $this->IndexIn++;
                return $Assoc;

            }
            else
            {
                return False;
            }
        }
        /**
        * Main Selector Function
        * @param mixed $Matches Tokens.
        */
        private function Select($Matches)
        {
            $getObj = $this->dotObjectToArray($Matches[self::AR_INDEX]);
            $Dizimiz = $getObj['array'];

            is_array($Dizimiz) || $this->Error(self::NOT_AN_ARRAY,$Matches[self::AR_INDEX]);

            $this->Dizimiz = $Dizimiz[$this->firstKey($Dizimiz)];
            $sart= $this->isComplicated ? $this->createWhereClause($Matches,$Dizimiz): true;

            foreach ($Dizimiz as $key=>$value)
            {
                if (is_array($value))
                    extract($value,EXTR_OVERWRITE);

                $SartSaglandi = false;
                @eval('$SartSaglandi = (boolean)('.$sart.');');
                if(!$this->isComplicated || $SartSaglandi)
                {
                    /* $this->ResultValues[] = $value;
                    $this->ResultKeys[] = $key; */
                    if((isset($Dizi) && is_array($Dizi)) || is_array($Dizi = $this->Dizimiz))
                        $Selected = $value;
                    $Selected['key'] = $key;
                    $Selected['value'] = $value;
                    $this->Selected[] = $Selected;
                    $this->AffecTedRows++;
                }
                unset($Dizimiz[$key]);
                if (is_array($value))
                    eval($this->unsetByArray(array_keys($value)));
            }
            # @since v1.1.0  MultiCols
            while($Fetched = $this->fetch())
                $this->Result[] = $Fetched;


            # @since v1.1.0  ORDER BY
            if($this->Tokens[self::OR_INDEX])
                $this->Result = $this->OrderArray($this->Result,$this->Tokens[self::OR_INDEX]);


            # @since v1.2.0   DISTINCT
            if($this->Tokens[self::UNQ_INDEX])

                $this->Result = $this->Uniquarray($this->Result);


            return $this;
        }
        /**
        * Return First key of given array.
        *
        * @param mixed $Array
        * @return mixed
        */
        private function firstKey($Array)
        {
            if(is_array($Array))
            {
                foreach ($Array as $FirstKey=>$FirstValue)
                {
                    return $FirstKey;
                }
            }
            else return 0;
        }

        /**
        * Sorter Function
        * @since v1.1.0
        *
        * @param array $array
        * @param string $cols
        */
        private function OrderArray($ArrayBeSorted,$OrderString)
        {
            $orCondCol = explode(',',$OrderString);
            foreach ($orCondCol as $ColAndOrder)
            {
                preg_match('/(.*)([\s]+)(ASC|DESC)/is',$ColAndOrder,$OrdCol);
                if(!array_key_exists(trim($OrdCol[1]),$this->Result[0]))
                {
                    $this->Error(self::UNKNOWN_COLUMN,trim($OrdCol[1]));
                }
                else
                {

                    $Columns[trim($OrdCol[1])] = 'SORT_'.strtoupper(trim($OrdCol[3]));

                }
            }
            $colarr = array();
            foreach ($Columns as $col => $order)
            {
                $colarr[$col] = array();
                foreach ($ArrayBeSorted as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
            }
            $runIt = 'array_multisort(';
            foreach ($Columns as $col => $order)
            {
                $runIt .= '$colarr[\''.$col.'\'],'.$order.',';
            }
            $runIt = substr($runIt,0,-1).');';
            eval($runIt);
            $SortedArray = array();
            foreach ($colarr as $col => $arr)
            {
                foreach ($arr as $k => $v)
                {
                    $k = substr($k,1);
                    if (!isset($SortedArray[$k])) $SortedArray[$k] = $ArrayBeSorted[$k];
                    $SortedArray[$k][$col] = $ArrayBeSorted[$k][$col];
                }
            }

            foreach ($SortedArray as $SortElem)
            {
                $ReSortedArray[] = $SortElem;
            }

            return $ReSortedArray;
        }

        /**
        * Unique Function for DISTINCT
        * @since v1.2.0
        */
        private function Uniquarray($Array)
        {
            $newArray = '';
            foreach ($Array as $Arraykey=>$ArrayValue)
            {
                $newArray[implode(',',$ArrayValue)] = $ArrayValue;
            }
            return is_array($newArray) ? array_values($newArray) : array();
        }

        /**
        * Main Deleter Function
        * @since v1.0.2
        * @param mixed $Matches Tokens.
        */
        private function Delete($Matches)
        {
            $getObj = $this->dotObjectToArray($Matches[self::AR_INDEX]);
            $Dizimiz = $getObj['array'];
            if(!is_array($Dizimiz))
            {
                $this->Error(self::NOT_AN_ARRAY,$Matches[self::AR_INDEX]);
            }
            if($this->isComplicated)
            {
                $sart = $this->createWhereClause($Matches,$Dizimiz);
            }
            else
            {
                $sart = true;
            }

            foreach ($Dizimiz as $key=>$value)
            {
                if (is_array($value)) extract($value,EXTR_OVERWRITE);
                @eval('$SartSaglandi = ('.$sart.');');
                if(!$this->isComplicated || $SartSaglandi)
                {
                    $deleteCode = 'global $'.$getObj['arrName'].'; unset($'.$getObj['global'].'['.$key.']);';

                    eval($deleteCode);
                    $this->AffecTedRows++;
                }
            }
            return $this;
        }

        /**
        * Insert Statement
        * Can input elements for all array.
        *
        * @since v1.3.0
        * @param mixed $Matches
        */
        private function Insert($Matches)
        {
            $Matches = $Matches ? $Matches : $this->Tokens;
            $gArray = $this->dotObjectToArray($this->Tokens[self::AR_INDEX]);
            $inCols = str_replace(array(')','('),'',$Matches[self::IN_COLS]);
            $colNames = explode(',',$inCols);
            $colVals = str_replace(array('(',')',')','\'','\,'),'',$Matches[self::IN_COLVALS]);
            $colValues = explode(',',$colVals);
            foreach ($colNames as $ind=>$Name)
            {
                $evuLateIt ='global $'.$gArray['arrName'].';$'.$gArray['global'].'['.(($Name == 'key' || $Name == 'value') ? $this->Escape($colValues[$this->FindIndByVal($colNames,'key')],true) :NULL).'] = '.(($Name == 'key' || $Name == 'value') ? '\''.$this->Escape($colValues[$this->FindIndByVal($colNames,'value')],true).'\';' :'array('.$this->ArrayToString($colNames,$colValues).');');
                @eval($evuLateIt);
                $this->AffecTedRows++;
                break;
            }
            return $this;
        }
        /**
        * Update Function
        * @since v1.4.0
        * @param mixed $Matches
        */
        private function Update($Matches)
        {
            $getObj = $this->dotObjectToArray($Matches[self::AR_INDEX]);
            $Dizimiz = $getObj['array'];
            if(!is_array($Dizimiz))
            {
                $this->Error(self::NOT_AN_ARRAY,$Matches[self::AR_INDEX]);
            }
            if($this->isComplicated)
            {
                $sart = $this->createWhereClause($Matches,$Dizimiz);
            }
            else
            {
                $sart = true;
            }

            foreach ($Dizimiz as $key=>$value)
            {
                if (is_array($value)) extract($value,EXTR_OVERWRITE);
                @eval('$SartSaglandi = ('.$sart.');');
                if(!$this->isComplicated || $SartSaglandi)
                {

                    $allCols = explode(',',$this->Escape($Matches[self::UP_COLVALS]));
                    foreach ($allCols as $collString)
                    {
                        preg_match('/(.*)=\'(.*)\'/is',$collString,$mTs);

                        switch($mTs[1])
                        {
                            case 'key':
                                if(array_key_exists($mTs[2],$getObj['array']))
                                {
                                    $this->Error(self::ALREADY_HAS_KEY,array($getObj['arrName'],$mTs[2]));
                                }
                                else
                                {
                                    $updateCode = 'global $'.$getObj['arrName'].'; $'.$getObj['global'].'['.$mTs[2].'] = $'.$getObj['global'].'['.$key.']; unset($'.$getObj['global'].'['.$key.']);';
                                }
                                break;
                            case 'value':
                                $updateCode = 'global $'.$getObj['arrName'].'; $'.$getObj['global'].'['.$key.']=\''.$mTs[2].'\';';
                                break;
                            default:
                                $updateCode = 'global $'.$getObj['arrName'].'; $'.$getObj['global'].'['.$key.']['.$mTs[1].']=\''.$mTs[2].'\';';
                                break;
                        }
                        eval($updateCode);
                    }
                    $this->AffecTedRows++;
                }
            }
            return $this;
        }
        /**
        * XML File To Array
        * @since v1.5.0
        * @param $fileName file Url
        */
        private function xml_file($fileName)
        {
            $varName = 'x'.md5($fileName);
            global $$varName,$$fileName;
            $$varName = $this->ObjectToArray((array)simplexml_load_string(isset($$fileName) ? $$fileName : $this->getContent($fileName)));
            $this->Tokens[self::AR_INDEX] = $varName.$this->Tokens['func_in'];
        }
        /**
        * XML string to array
        * @since v1.5.5
        * @param mixed $variableName variable name as string
        */
        private function xml_string($variableName)
        {
            $this->xml_file($variableName);
        }
        /**
        * Object To array Recursive
        * @since v1.5.0
        * @param mixed $data
        * @return mixed
        */
        private function ObjectToArray($data)
        {
            $nData = array();
            foreach ((array)$data as $k=>$v)
            {
                $nData[$k] = (is_object($v) || is_array($v)) ? self::ObjectToArray($v): $v;
            }
            return $nData;
        }
        /**
        * Convert Where Clause to a PHP Code can return Boolean
        * @since v1.5.1
        * @param mixed $Matches
        * @param mixed $Dizimiz
        * @return mixed
        */
        private function createWhereClause($Matches,$Dizimiz)
        {
            $sart = $Matches[self::CM_WHINDEX];
            $sart = preg_replace('/(key|value'.(is_array($Dizimiz[$this->firstKey($Dizimiz)])?'|'.implode('|',array_keys($Dizimiz[$this->firstKey($Dizimiz)])):null).')(=|==|<|>|<=|>=)/i','$$1$2',$sart);
            $Find = array('key=','value=','\'.key.\'','\'.value.\'');
            $Replace = array('key==','value==','$key','$value');
            $sart = str_replace($Find,$Replace,$sart);
            if (is_array($Dizimiz[$this->firstKey($Dizimiz)]))
            {
                foreach ($Dizimiz[$this->firstKey($Dizimiz)] AS $keyName=>$keyVal)
                {
                    $Find = array($keyName.'=','\'.'.$keyName.'.\'');
                    $Replace = array($keyName.'==','$'.$keyName);
                    $sart = str_replace($Find,$Replace,$sart);
                }
            }

            # preg_match_all('/(key|value'.(is_array($Dizimiz[$this->firstKey($Dizimiz)])?'|'.implode('|',array_keys($Dizimiz[$this->firstKey($Dizimiz)])):null).')([\s]+)(not\s+)?(LIKE)*([\s]+)(\')*([^\'\n]+)(\')*/i',$sart,$likeMatches);

            /*
            foreach ($likeMatches[0] AS $ind=>$cm){
            $sart = (trim($likeMatches[3][$ind])?'!' :null).'preg_match(\'/^'.str_replace('%','(.*)',$likeMatches[7][$ind]).'$/i\',$'.$likeMatches[1][$ind].')';
            }
            */
            $pattern = '/(key|value'.(is_array($Dizimiz[$this->firstKey($Dizimiz)])?'|'.implode('|',array_keys($Dizimiz[$this->firstKey($Dizimiz)])):null).')([\s]+)(not\s+)?(LIKE)*([\s]+)(\')*([^\'\n]+)(\')*/i';
            $sart = preg_replace($pattern,'(trim(\'$3\')?\'!\' :null).preg_match(\'/^\'.str_replace(\'%\',\'(.*)\',preg_quote(\'$7\')).\'$/i\',$$1)',$sart);
            return $sart;

        }
        /**
        * HTML Tags To an Array
        * @since v1.5.1
        * @param mixed $fileName
        */
        private function html_file($fileName)
        {
            $varName = 'h'.md5($fileName);
            global $$varName;
            $Tags = Array();
            $Attributes = Array();
            $Values = Array();
            $DataBase = Array();

            $this->MatchTags($this->getContent($fileName),$Tags,$Attributes,$Values);

            foreach ($Tags as $Tind=>$TagName)
            {
                $DataBase[$TagName][$Tind] = array("tag_id"=>$Tind,'innerHTML'=>$Values[$Tind],'text'=>strip_tags($Values[$Tind]));
                if(preg_match_all('/([A-Z0-9]+)=((\'|")?([^\'"]+)(\'|")?)/is',$Attributes[$Tind],$MatchedAttr))
                {
                    if(count($MatchedAttr[0])>0)
                    {
                        for ($i = 0; $i < count($MatchedAttr[0]); $i++)
                        {
                            $DataBase[$TagName][$Tind][$MatchedAttr[1][$i]] = $MatchedAttr[4][$i];
                        }
                    }
                }
                unset($Tags[$Tind],$Attributes[$Tind],$Values[$Tind]);
            }
            $$varName = $DataBase;
            unset($DataBase);
            $this->Tokens[self::AR_INDEX] = $varName.$this->Tokens['func_in'];
        } function MatchTags($htmlCode, &$Tags, &$Attributes, &$Values)
        {
            preg_match_all('/<([a-z0-9\-]+)([^>]+)?>(([^\<]+)?<\/\1>)?/i', $htmlCode, $Matches);
            if (count($Matches[0]) != 0)
            {
                for ($i = 0; $i < count($Matches[0]); $i++)
                {
                    $Tags[] = strtolower(trim($Matches[1][$i]));
                    $Attributes[] = trim($Matches[2][$i]);
                    $Values[] = trim($Matches[4][$i]);
                    if (trim($Matches[4][$i]) != '' and preg_match('/<[a-z0-9\-]+.*?>/is', $Matches[4][$i]))
                    {
                        $this->MatchTags($Matches[4][$i], $Tags, $Attributes, $Values);
                    }

                }
            }

        }
        /**
        * Read file(s)? or director(i|y)(es)? names from a diretory   | regexp :D
        * @since v1.5.3
        */
        private function dir_file($dirName)
        {
            $this->dir_files($dirName);
        }
        private function dir_files($dirName)
        {
            $varName = 'd'.md5($dirName);
            global $$varName;
            if (!is_dir($dirName)){
                $this->Error(self::NOT_A_DIR,$dirName);
                return array();
            }
            $filesFolders = array();
            $dirHandle = opendir($dirName);
            $fileID = 0;
            while ($Chield = readdir($dirHandle))
            {
                $cat = is_dir($dirName.'/'.$Chield)? 'dirs' : 'files';
                $filesFolders[$cat][$fileID]['name']    =$Chield;                           # File or folder name
                $filesFolders[$cat][$fileID]['ctime']   =filectime($dirName.'/'.$Chield);   # Creation Time
                $filesFolders[$cat][$fileID]['atime']   =fileatime($dirName.'/'.$Chield);   # Last Access Time
                $filesFolders[$cat][$fileID]['mtime']   =filemtime($dirName.'/'.$Chield);   # Last Modification Time
                $filesFolders[$cat][$fileID]['size']    =filesize($dirName.'/'.$Chield);    # File Size as Byte
                $filesFolders[$cat][$fileID]['type']    =filetype($dirName.'/'.$Chield);    # File or Folder
                $filesFolders[$cat][$fileID]['ext']     =end(explode('.',$Chield));         # File Extension
                $fileID++;
            }
            closedir($dirHandle);

            if ($this->Tokens['func_in']!= '.dirs' && $this->Tokens['func_in']!= '.files')
                $$varName = array_merge($filesFolders['dirs'],$filesFolders['files']);
            else
                $$varName = $filesFolders;

            $this->Tokens[self::AR_INDEX] = $varName.$this->Tokens['func_in'];
        }

        /**
        * Json File to an Array
        * @since v1.5.0
        * @param mixed $fileName
        */
        private function json_file($fileName)
        {
            $varName = 'j'.md5($fileName);
            global $$varName;
            $$varName = $this->ObjectToArray(($jsDecode = json_decode($Content = $this->getContent($fileName)))? $jsDecode : unserialize($Content));
            $this->Tokens[self::AR_INDEX] = $varName.$this->Tokens['func_in'];
        }
        /**
        * File Lines To Array
        * @since v1.5.0
        * @param mixed $fileName
        */
        private function text_file($fileName)
        {
            $varName = 't'.md5($fileName);
            global $$varName;

            $lines = $this->getContent($fileName,true);
            $newArray = Array();
            foreach ($lines AS $lineNum=>$Text){
                $newArray[] = array('line'=>$lineNum,'text'=>$Text);
            }

            $$varName = $newArray;
            unset($newArray);
            $this->Tokens[self::AR_INDEX] = $varName.$this->Tokens['func_in'];
        }
        /**
        * get Contents From an url or a file
        *
        * @param mixed $url
        * @return string
        */
        private function getContent($url,$File = False)
        {
            return function_exists($this->GetContentsFunc) ? call_user_func($this->GetContentsFunc,$url) : ($File ? @file($url) : @file_get_contents($url));
        }

        /**
        * Return a String as an array build for eval();
        * @since v1.3.0
        *
        * @param mixed $KeyArr
        * @param mixed $ValArr
        * @return string
        */
        private function  ArrayToString($KeyArr,$ValArr)
        {
            $elems = array();
            foreach ($KeyArr AS $ind=>$Val)
            {
                $elems[] = '"'.$Val.'"=>"'.$this->Escape($ValArr[$ind],true).'"';
            }
            return implode(',',$elems);
        }

        /**
        * Deactive Escape Characters
        * @since v1.3.0
        *
        * @param mixed $String
        * @param mixed $UnEscape
        */
        private function Escape($String,$UnEscape = False)
        {
            $Unex = array('\(','\\\'','\)','\,');
            $Ex = array('D3Linq::ExedRPhar','D3Linq::ExedQuot','D3Linq::ExedRPhar','D3Linq::ExedVir');
            return $UnEscape ? str_replace($Ex,$Unex,$String) : str_replace($Unex,$Ex,$String);
        }

        /**
        * Find First Value index what you given.
        *
        * @since v1.3.0
        *
        * @param mixed $Array
        * @param mixed $Val
        * @return mixed
        */
        function FindIndByVal($Array,$Val)
        {
            return array_search($Val,$Array);
        }

        /**
        * Error Creating Function
        *
        * @param mixed $String Error Template
        * @param mixed $Vals  Replace Values
        */
        private function Error($String,$Vals = False)
        {
            $ErrAndCode =explode('|',$String);
            $Error      =strip_tags($ErrAndCode[1]);
            $ErrCode    =$ErrAndCode[0];

            if(is_array($Vals))
            {
                foreach($Vals AS $key=>$value)
                {
                    $Error = str_replace('%'.($key+1),$value,$Error);
                }
            }
            elseif($Vals)
            {
                $Error = str_replace('%1',$Vals,$Error);
            }
            $this->ErrorP($Error,$ErrCode);

        }
        /**
        * Error Printing Function.
        *
        * @param mixed $String  Error Message
        * @param mixed $Code    Error Code
        */
        private function ErrorP($String,$Code = 000)
        {
            $this->ErrorMsg  =$String;
            $this->ErrorCode =$Code;
            if($this->OnError)
            {
                if($this->OnError == 'selfdebug')
                {
                    $this->debug();
                }
                else
                {
                    $this->RunEvent($this->OnError);
                }
            }else{
                die('<div><b>#'.$Code.'</b> '.$String."</div>\n");
            }
        }
        /**
        * Return First of Given Array
        *
        * @param mixed $Array
        * @return mixed  First of Array Element
        */
        private function FirstOf($Array)
        {
            foreach ($Array as $Val)
                return $Val;


        }
        /**
        * Find Char Count in a string
        *
        * @param mixed $Needle
        * @param mixed $Haystack
        * @return int  Char Count
        */
        private function CountIn($Needle,$Haystack)
        {
            preg_match_all('/([^\\\]['.$Needle.'])/i',$Haystack,$Found);
            return count($Found[1]);
        }
        /**
        * Event Runner
        *
        * @param mixed $EventName Func Name
        * @param mixed $Param  Func Param(s)
        * @return mixed Func Result which called
        */
        private function RunEvent($EventName,$Param = False)
        {
            if(function_exists($EventName)){
                if($Param){

                    if(is_array($Param)){
                        return call_user_func_array($EventName,$Param);

                    } else {
                        return call_user_func($EventName,$Param);
                    }

                }else {
                    return call_user_func($EventName);
                }
            }
        }
        /**
        * Method Caller
        * @since 1.5.0
        * @param mixed $MethodName Method Name
        * @param mixed $Param  Method Param(s)
        * @return mixed Method Result which called
        */
        private function Call($MethodName,$Param = False)
        {
            if(method_exists($this,$MethodName)){
                if($Param){
                    if(is_array($Param)){
                        return call_user_method_array($EventName,$this,$Param);

                    } else {
                        return call_user_method($EventName,$this,$Param);
                    }

                }else {
                    return call_user_method($EventName,$this);
                }
            }else {

                $this->Error(self::UNSUPPORTED_FUNCTION,$MethodName);
            }
        }
        /**
        * Get Last D3Linq Error Message
        * @return String
        */
        public function ErrorMsg()
        {
            return $this->ErrorMsg;
        }
        /**
        * GEt Last D3Linq Error Code
        * @return String
        */
        public function ErrorCode()
        {
            return $this->ErrorCode;
        }

        /**
        * Debugger Funciton
        * @since v1.5.1
        * Print Debug Information about query you wroten
        * See
        *     - Errors
        *     - Invalid Token
        *     - Valid Tokens
        */
        public function debug()
        {
            if($this->DebugDeep>$this->DebugDeepLimit) return false;
            global $Tokens;
            $Tokens = $this->Tokens;
            $db = new D3Linq();
            $db->DebugDeep = $this->DebugDeep = $this->DebugDeep+1;
            $db->OnError = false;  // no repeat no lag (:
            $db->Query("SELECT *FROM Tokens WHERE !is_numeric('.key.') AND trim('.value.')!=''");
            $KnownTokens = $db->num_rows();
            echo "
            <style type=\"text/css\">\n
            .d3linq_debug{\n
            font-size:11px;\n
            font-family:Tahoma;\n
            border:#aaa 1px solid;\n
            }\n
            .thead,.thead td{\n
            background:#ddd;\n
            color:#000;\n
            padding:3px;\
            border:1px solid;\n
            font-size:12px;\n
            font-weight:700; \n
            }\n
            .row1,.row1 td{\n
            background:#eee;\n
            padding:3px;\n
            font-size:11px;\n
            }\n
            .row0,.row0 td{\n
            background:#efefef;\n
            padding:3px;\n
            font-size:11px;\n
            }\n
            .hata,.hata td{\n
            background:#f8ced8;\n
            color:#b1136c;\n
            font-size:11px;\n
            border:1px solid;\n
            padding:3px;\n
            }\n
            </style>\n";
            $arrName = $this->FirstOf(explode('.',$this->Tokens[self::AR_INDEX]));
            $SelectedArray = (isset($$arrName) && is_array($$arrName)) ?  $this->dotObjectToArray($this->Tokens[self::AR_INDEX]):array('global'=>'Unknown');
            echo '<table width="100%" class="d3linq_debug">'."\n"
            .'<tr class="thead"><td colspan="2" align="center">D3Linq Debug</td></tr>'."\n";
            if($this->ErrorCode){
                echo '<tr class="hata"><td><b>Error</b></td><td>'.$this->ErrorMsg().' Code : '.$this->ErrorCode().'</td><tr>';
            }
            echo '<tr class="row1"><td>SQL Text</td><td>'.$this->LastSQL.'</td></tr>'."\n";
            echo '<tr class="row2"><td>Valid Tokens Count</td><td>'.$KnownTokens.'</td></tr>'."\n";
            echo '<tr class="row2"><td>Array</td><td>$'.$SelectedArray['global'].'</td></tr>'."\n";
            echo '<tr class="row1"><td>Affected Rows/Array Count</td><td>'.$this->affected_rows().'/'.count(isset($SelectedArray['array'])?$SelectedArray['array']:array()).'</td></tr>'."\n";
            echo '<tr class="row1"><td valign="top">Tokens</td><td>';
            echo '<table width="100%" class="d3linq_debug">'."\n";
            echo '<tr class="row1"><td><b>Token Name</b></td><td><b>Value</b></td></tr>'."\n";
            $r = 1;
            while ($t = $db->fetch_object())
            {
                echo '<tr class="row'.($r%2).'"><td>'.strtoupper($t->key).'</td><td>'.$this->Tokens[$t->key].'</td></tr>'."\n";
                $r++;
            }
            if($this->ErrorCode)
            {
                $sql = $this->LastSQL;
                reset($this->wagons);
                while ($token = each($this->wagons)){
                    $nToken[] = $token[1];
                    if (!preg_match('/'.$this->CreateTrain($nToken).'/i',$sql,$matched)){
                        echo '<tr class="hata"><td>Error on<br><b>'.strtoupper($token['key']).'</b></td><td><br>'.$token[1]['msg'].'<br></td></tr>';
                        break;
                    }
                    unset($nToken,$Tokens);
                }
            }
            echo '</table></td></tr>'."\n";
            echo '</table>';
        }

        /**
        * ==================================
        *  D3Linq TOOLS
        * ==================================
        * @since v1.5.2
        */

        /**
        * Get Result Of Last Query as an array
        * @since v1.5.2
        */
        public function getResult()
        {
            return $this->Result;
        }
        /**
        * Get Last Generated Global Array name for using in another query again.
        * @since v1.5.2
        */
        public function getArrayName()
        {
            return $this->Tokens[self::AR_INDEX];
        }

        /**
        * Array Rebuild Can redecorate the given array as tamplte you given . use array_map as template
        * @since v1.5.2
        */
        public function arrayRebuild(array $Array,$ForceArrayElement = False,$template = '$value = $value;$key = $key;')
        {
            $newarray = array();
            foreach ($Array AS $key=>$value)
            {
                if(!$ForceArrayElement)
                    eval($template);
                if(is_array($value))
                {
                    $value = self::arrayRebuild($value,false,$template);
                }
                if($ForceArrayElement)
                    eval($template);
                $newarray[is_array($key)?implode('|',$key):$key] = $value;
            }
            return $newarray;
        }
        /**
        * Unset Extracted Variables by Array
        * @param mixed $Array
        * @since v1.5.4
        */
        public function unsetByArray($Array)
        {
            return (is_array($Array) && !in_array(0,$Array)) ? 'unset($'.implode(',$',$Array).')' : '';
        }


        /**
        * print query result in a html table
        *
        * @param mixed $Array
        * @return d3Grid
        */
        public function grid($Array=false)
        {
            class_exists('D3Grid',true) || $this->Error('Class Not found: D3Grid');
            if (!is_a($this->grid,'D3Grid'))
            {
                if(!is_array($Array))
                    $Array = $this->fetch_all();

                if(is_Array($Array))
                {
                    $this->grid = new d3Grid($Array);
                }
                else
                    $this->Error('this is not an array for generate a grid');
            }
            else
            {
                if(is_array($Array))
                {
                    $this->grid = new d3Grid($Array);
                }
            }

            return $a=&$this->grid;
            return new d3Grid(array()); # IDE Hack
        }

    }
?>