<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Auction
 *
 * @author mmalicki
 */
class Auction {
    //put your code here
    public $Id;
    public $productTitle;
    public $brand;
    public $productCode;
    public $rewardPoints;
    public $shortDesc;
    public $longDesc;
    public $picture;
    public $startPrice;
    public $bidStep;
    public $postage;
    public $reservePrice;
    public $buyItNowPrice;
    public $actualPrice;
    public $endPrice;
    public $winningUser;
    public $active;
    public $startDate;
    public $endDate;
    public $category;
    public $status;
    
    public $highestBid;
    public $finalPrice;
    public $winner;
    public $looser;
    public $number_of_bids;
	public $instanceId;
    
	public function __construct(){
		$this->winner = null;
		$this->looser = null;
	}
	
    public function get_number_of_bids(&$dba){
		$this->number_of_bids = $dba->getRowsCount('bids', array('auction' => $this->Id));
        return $this->number_of_bids;
    }
    
    public function selfDestroy(&$dba){
		$dba->delete('bids', array('auction' => $this->Id));
        $dba->delete('auctions', array('Id' => $this->Id));
        unset($this);
    }
    
    
    public function getHighestBid(&$dba){
		$_bids = $dba->selectArrayClass('bids', 'Bid', array('auction' => $this->Id), null, "ORDER BY id DESC LIMIT 2");
		//$highestBid = $dba->selectSingleClass('bids', 'Bid', array('auction' => $this->Id), null, "ORDER BY id DESC LIMIT 1");
		//$loosingBid = $dba->selectSingleClass('bids', 'Bid', array('auction' => $this->Id), null, "ORDER BY id DESC LIMIT 2, 1");
		$highestBid = null;
		$loosingBid = null;
        
		$_count = count($_bids);
		
		if($_count == 1){
			$highestBid = current($_bids);	
		}else if($_count == 2){
			$highestBid = current($_bids);
			$loosingBid = end($_bids);			
		}
		
		
        if($highestBid == null){
            $this->highestBid = $this->startPrice;
            
        }else{
            $this->highestBid = $highestBid->bid;
			$this->winner = $dba->selectSingleClass('users', 'User', array('Id' => $highestBid->userId), null);
            if($loosingBid != null){
				$this->looser = $dba->selectSingleClass('users', 'User', array('Id' => $loosingBid->userId), null);
            }
        }
        
        return $this->highestBid;
    }
    
    public function getEndPrice(&$dba){
        
        if($this->active == 3){
            $this->finalPrice = $this->endPrice;
			$this->winner = $dba->selectSingleClass('users', 'User', array('Id' => $this->winningUser), null);
        }else{
            $this->finalPrice = $this->getHighestBid($dba);
        }   
        return $this->finalPrice;
    }
    
    public function saveBIN(&$database){
        $data = array('active' => $this->active, 'winningUser' => $this->winningUser, 'endPrice' => $this->endPrice, 'endDate' => $this->endDate);
		return $database->update('auctions', $data, array('Id' => $this->Id, 'active' => '1'));
    }
    
    public function buildRelist(){
        $data = array();
        $data['productTitle'] = $this->productTitle;
        $data['brand'] = $this->brand;
        $data['productCode'] = $this->productCode;
        $data['category'] = $this->category;
        $data['shortDesc'] = $this->shortDesc;
        $data['longDesc'] = $this->longDesc;
        $data['picturePath'] = $this->picturePath;
		$data['active'] = $this->active;
        $data['bidStep'] = $this->bidStep;
        $data['postage'] = $this->postage;
        $data['startPrice'] = $this->startPrice;
        $data['reservePrice'] = $this->reservePrice;
        $data['buyItNowPrice'] = $this->buyItNowPrice;
        
        $end = new DateTime($this->endDate);
        $start = new DateTime($this->startDate);
        $diff2now = $start->diff(new DateTime(date('Y-m-d H:i:s')))->days;
        $diff2start = $end->diff($start)->days;
        $interval = $diff2now + $diff2start;
        
        $start->add(new DateInterval('P' . $diff2now . 'D'));
        $end->add(new DateInterval('P' . $interval . 'D'));
        
        $data['startDate'] = $start->format('Y-m-d H:i:s');
        $data['endDate'] = $end->format('Y-m-d H:i:s');        
        
        return $data;
    }
    
    public function relistByDates(&$database, $now = true){
        $end = new DateTime($this->endDate);
        $start = new DateTime($this->startDate);
        $diff2now = $start->diff(new DateTime(date('Y-m-d H:i:s')))->days;
        $diff2start = $end->diff($start)->days;
        
        if($now){
            $start = new DateTime(date('Y-m-d H:i:s'));
            $end = new DateTime(date('Y-m-d H:i:s'));
            $end->add(new DateInterval('P' . $diff2start . 'D'));
        }else{
            $interval = $diff2now + $diff2start;
            $start->add(new DateInterval('P' . $diff2now . 'D'));
            $end->add(new DateInterval('P' . $interval . 'D'));
        }
        
        $this->startDate = $start->format('Y-m-d H:i:s');
        $this->endDate = $end->format('Y-m-d H:i:s');        
        
		return $database->update('auctions', array('startDate' => $this->startDate, 'endDate' => $this->endDate), array('Id' => $this->Id));
        
    }
    
}
