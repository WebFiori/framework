<?php
namespace phpStructs;
/**
 * The base node class that can be used to construct different data structures.
 * @author Ibrahim
 * @version 1.1
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
     * @param Node $next The next node. If NULL is given or the given 
     * value is not an instance of Node, the next node will be set to 
     * NULL.
     * @since 1.0
     */
    public function __construct(&$data,&$next=null) {
        $this->setData($data);
        $this->setNext($next);
    }
    /**
     * Returns the data that is stored in the node.
     * @return mixed The data that is stored in the node.
     * @since 1.0
     */
    public function &data(){
        return $this->data;
    }
    /**
     * Returns a reference to the next linked node. 
     * @return mixed If no linked node is set, NULL is returned. Else, 
     * an instance of Node is returned.
     * @since 1.0
     */
    public function &next(){
        return $this->next;
    }
    /**
     * Sets the data that the node will hold.
     * @param mixed $data A reference to the data that the node will hold.
     * @since 1.0
     */
    public function setData(&$data){
        $this->data = $data;
    }
    /**
     * Sets the reference to the next linked node.
     * @param Node $next The next node. If NULL is given, the next node 
     * will be set to NULL. If the given value is not an instance of Node, 
     * it will be not set.
     * @since 1.0
     */
    public function setNext(&$next){
        if($next instanceof Node){
            $this->next = $next;
        }
        else if($next == NULL){
            $this->next = NULL;
        }
    }
}