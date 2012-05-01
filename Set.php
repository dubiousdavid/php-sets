<?


class Set
{
    protected $label;
    protected $elements = array();
    
    public function __construct($label)
    {
        $this->setLabel($label);
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
    
    public function __toString()
    {
        return $this->label;
    }
    
    //Add element(s)
    
    public function add($elements)
    {
        if(is_array($elements))
        {
            foreach($elements as $element){
                $this->elements[$element] = NULL;
            }
        }
        else if(get_class($elements) == __CLASS__)
        {
            foreach($elements->get() as $element){
                $this->elements[$element] = NULL;
            }
        }
        else{
            $this->elements[$elements] = NULL;
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
    
    //Union
    
    public function union()
    {
        $resultSet = new self();
        $resultSet->add($this->get());
        
        $labels[] = $this->getLabel();
        
        foreach(func_get_args() as $arg)
        {
            $labels[] = $arg->getLabel() ?: "unlabeled";
            $resultSet->add($arg);            
        }
        
        return $resultSet->setLabel("(Union of ".implode(", ", $labels).")");
    }
    
    //Intersection
    
    public function intersection()
    {
        $params[] = $this->get();
        $labels[] = $this->getLabel();
        
        foreach(func_get_args() as $arg)
        {
            $labels[] = $arg->getLabel() ?: "unlabeled";
            $params[] = $arg->get();
        }
        
        $resultSet = new self();
        
        return $resultSet->setLabel("(Intersection of ".implode(", ", $labels).")")
            ->add(call_user_func_array("array_intersect", $params));
    }
    
    //Relative complement
    
    public function relComp()
    {
        $params[] = $this->get();
        
        foreach(func_get_args() as $arg)
        {
            $labels[] = $arg->getLabel() ?: "unlabeled";
            $params[] = $arg->get();
        }
        
        $resultSet = new self();
        
        return $resultSet->setLabel("(Relative complement of ".$this->getLabel()." in ".implode(", ", $labels).")")
            ->add(call_user_func_array("array_diff", $params));
    }
    
    //Symetric difference
    
    public function symDiff()
    {
        //All sets
        $sets[] = $this->get();
        
        $labels[] = $this->getLabel();
        
        foreach(func_get_args() as $arg)
        {
            $labels[]   = $arg->getLabel() ?: "unlabeled";
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
