<?php
/**
 * The base node class that can be used to construct different data structures.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class Node{
    /**
     * The next node.
     * @var Node
     * @since 1.0 
     */
    private $next;
    /**
     * The data that the node is holding.
     * @var mixed
     * @since 1.0 
     */
    private $data;
    /**
     * Constructs a new node with specific data and next node.
     * @param mixed $data The data that the node will hold.
     * @param Node $next The next node. If <b>NULL</b> is given or the given 
     * value is not an instance of <b>Node</b>, the next node will be set to 
     * <b>NULL</b>.
     * @since 1.0
     */
    public function __construct($data,$next=null) {
        $this->setData($data);
        $this->setNext($next);
    }
    /**
     * Returns the data that is stored in the node.
     * @return mixed The data that is stored in the node.
     * @since 1.0
     */
    public function data(){
        return $this->data;
    }
    /**
     * Returns the next linked node. 
     * @return mixed If no linked node is set, <b>NULL</b> is returned. Else, 
     * an instance of <b>Node</b> is returned.
     * @since 1.0
     */
    public function next(){
        return $this->next;
    }
    /**
     * Sets the data that the node will hold.
     * @param mixed $data The data that the node will hold.
     * @since 1.0
     */
    public function setData($data){
        $this->data = $data;
    }
    /**
     * Sets the next linked node.
     * @param Node $next The next node. If <b>NULL</b> is given, the next node 
     * will be set to <b>NULL</b>. If the given value is not an instance of <b>Node</b>, 
     * it will be not set.
     * @since 1.0
     */
    public function setNext($next){
        if($next instanceof Node){
            $this->next = $next;
        }
        else if($next == NULL){
            $this->next = NULL;
        }
    }
}