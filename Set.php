<?


class Set
{
    protected $label;
    protected $universe;
    protected $elements    = array();
    const UNIVERSE_ERROR   = "The set '%s' is in the '%s' universe, but the set '%s' is in the '%s' universe";
    
    public function __construct($label = "undefined", $universe = "undefined")
    {
        $this->label    = $label;
        $this->universe = $universe;
    }
    
    public function setLabel($label)
    {
        $this->label = $label;
        
        return $this;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setUniverse($universe)
    {
        $this->universe = $universe;
        
        return $this;
    }
    
    public function getUniverse()
    {
        return $this->universe;
    }
    
    public function __toString()
    {
        return $this->label;
    }
    
    //Add element(s) to this set.  No universe check.
    
    public function add($elements)
    {
        if(is_array($elements))
        {
            foreach($elements as $element){
                $this->elements[(string) $element] = NULL;
            }
        }
        else if(get_class($elements) == __CLASS__)
        {
            foreach($elements->get() as $element){
                $this->elements[(string) $element] = NULL;
            }
        }
        else{
            $this->elements[(string) $elements] = NULL;
        }
        
        return $this;
    }
    
    //Get set elements
    
    public function get()
    {
        return array_keys($this->elements);
    }
    
    //To array
    
    public function toArray()
    {
        return $this->get();
    }
    
    //Is this set a subset of all the arguments. All sets must be in the same universe.
    
    public function isSubset()
    {
        $elements = $this->get();
        
        foreach(func_get_args() as $arg)
        {
            //Check if the sets are in the same universe
            if(($universe = $arg->getUniverse()) != $this->universe)
            {
                throw new Set_Exception(
                    sprintf(self::UNIVERSE_ERROR, $arg->getLabel(), $universe, $this->label, $this->universe)
                );
            }
            
            $set = $arg->get();
            
            foreach($elements as $element)
            {
                if(!in_array($element, $set)){
                    return false;
                }
            }
        }
        
        return true;
    }
    
    //Union of this set and all arguments. All sets must be in the same universe.
    
    public function union()
    {
        $resultSet = new self();
        $resultSet->add($this->get());
        
        $labels[] = $this->label;
        
        foreach(func_get_args() as $arg)
        {
            //Check if the sets are in the same universe
            if(($universe = $arg->getUniverse()) != $this->universe)
            {
                throw new Set_Exception(
                    sprintf(self::UNIVERSE_ERROR, $arg->getLabel(), $universe, $this->label, $this->universe)
                );
            }
            
            $labels[] = $arg->getLabel();
            $resultSet->add($arg);            
        }
        
        return $resultSet->setLabel("(Union of ".implode(", ", $labels).")");
    }
    
    //Intersection of this set and all arguments. All sets must be in the same universe.
    
    public function intersection()
    {
        $params[] = $this->get();
        $labels[] = $this->label;
        
        foreach(func_get_args() as $arg)
        {
            //Check if the sets are in the same universe
            if(($universe = $arg->getUniverse()) != $this->universe)
            {
                throw new Set_Exception(
                    sprintf(self::UNIVERSE_ERROR, $arg->getLabel(), $universe, $this->label, $this->universe)
                );
            }
            
            $labels[] = $arg->getLabel();
            $params[] = $arg->get();
        }
        
        $resultSet = new self();
        
        return $resultSet->setLabel("(Intersection of ".implode(", ", $labels).")")
            ->add(call_user_func_array("array_intersect", $params));
    }
    
    //Relative complement of this set and all arguments. All sets must be in the same universe.
    
    public function relComp()
    {
        $params[] = $this->get();
        
        foreach(func_get_args() as $arg)
        {
            //Check if the sets are in the same universe
            if(($universe = $arg->getUniverse()) != $this->universe)
            {
                throw new Set_Exception(
                    sprintf(self::UNIVERSE_ERROR, $arg->getLabel(), $universe, $this->label, $this->universe)
                );
            }
            
            $labels[] = $arg->getLabel();
            $params[] = $arg->get();
        }
        
        $resultSet = new self();
        
        return $resultSet->setLabel("(Relative complement of ".$this->label." in ".implode(", ", $labels).")")
            ->add(call_user_func_array("array_diff", $params));
    }
    
    //Symetric difference of this set and all arguments. All sets must be in the same universe.
    
    public function symDiff()
    {
        //All sets
        $sets[] = $this->get();
        
        $labels[] = $this->label;
        
        foreach(func_get_args() as $arg)
        {
            //Check if the sets are in the same universe
            if(($universe = $arg->getUniverse()) != $this->universe)
            {
                throw new Set_Exception(
                    sprintf(self::UNIVERSE_ERROR, $arg->getLabel(), $universe, $this->label, $this->universe)
                );
            }
            
            $labels[]   = $arg->getLabel();
            $sets[]     = $arg->get();
        }
        
        //# of sets
        $numSets = count($sets);
        
        //Create matrix
        for($i = 0; $i < $numSets; $i++)
        {
            $indexes = range(0, $numSets - 1);
            unset($indexes[$i]);
            array_unshift($indexes, $i);
            
            for($j = 0; $j < $numSets; $j++){
                $matrix[$i][$j] = $sets[$indexes[$j]];
            }
        }
        
        //Calculate difference
        $resultSet = new self();
        $resultSet->setLabel("(Symetric difference of ".implode(", ", $labels).")");
        
        foreach($matrix as $row){
            $resultSet->add(call_user_func_array("array_diff", $row));
        }
        
        return $resultSet;
    }
}

class Set_Exception extends Exception
{
	
}









