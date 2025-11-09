<?php
class RAYMAND_GOODSGROUP {

    function goods_groups_child($childs , $goods_groups)
    {
        $result = [];
        foreach ($childs as $item)
        {
            $_childs = array_filter(
                                    $goods_groups, 
                                    function($val) use ($item) { 
                                        return $val->parent_id == $item->id;
                                    });
			$obj = (object) [
				'id'=> $item->id,
				'parent_id'=> $item->parent_id,
				'code'=> $item->code,
				'title'=> $item->title,
				'raymand_group_id'=> $item->raymand_group_id,
				'store_group_id'=> $item->store_group_id,
				'level'=> $item->level,
				'parent_store_id'=> $item->parent_store_id,
				'children'=> count($_childs) > 0 ? $this->goods_groups_child($_childs, $goods_groups) : []
            ];
			array_push($result, $obj);
        }
        return $result ;
    }

    public function get_goods_groups( $request = null ) 
    {

        global $wpdb;
        $sql = "update wp_raymand_goods_groups AS g inner JOIN
                (SELECT p.term_id,g.store_group_id FROM wp_raymand_goods_groups g 
                    LEFT JOIN (SELECT t.* from wp_terms t
                                LEFT JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
                                WHERE tt.taxonomy = 'product_cat' ) p ON g.store_group_id = p.term_id
                    WHERE g.store_group_id IS NOT NULL
                ) AS w ON g.store_group_id = w.store_group_id
                SET g.store_group_id = w.term_id";
        $wpdb->query($sql);

        global $wpdb;
            $sql = "update wp_raymand_goods_groups g
                    INNER JOIN (SELECT gd.id,g.store_group_id FROM wp_raymand_goods_groups g
                                    LEFT JOIN wp_raymand_goods_groups gd ON gd.parent_id = g.id
                                ) t ON t.id = g.id SET g.parent_store_id = t.store_group_id"; 
            $wpdb->query($sql);

		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wp_raymand_goods_groups order by id") );
        $goods_groups= [];
        $filteredParentArray = array_filter(
            $result, 
            function($val){ 
              return $val->parent_id == null && $val->level == 5380;
           });
		foreach($filteredParentArray as $item)
		{
			$childs = $wpdb->get_results
                        ( 
                            $wpdb->prepare( "SELECT * FROM wp_raymand_goods_groups where parent_id = $item->id order by id") 
                        );
			$obj = (object) [
				'id'=> $item->id,
				'parent_id'=> $item->parent_id,
				'code'=> $item->code,
				'title'=> $item->title,
				'raymand_group_id'=> $item->raymand_group_id,
				'store_group_id'=> $item->store_group_id,
                'level'=> $item->level,
                'parent_store_id'=> $item->parent_store_id,
				'children'=> count($childs) > 0 ? $this->goods_groups_child($childs, $result) : []
            ];
			array_push($goods_groups, $obj);
		}
		return array(
			'code' => 1000,
			'data' => $goods_groups,
			'message' => 'Get GoodsGroup Successfully'
		) ;

	}

    public function set_goods_groups_old($request = null) 
    {
        
        $message = '';
        $sql = '';
        $data = null;
        $code = 1000;
        $args = array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $request['token']
                )
            );
        $response = wp_remote_get( $request['base_url'] . '/BehtaShopWebService/api/GetGoodsGroupList',$args );
        
        if(wp_remote_retrieve_response_code( $response ) == 200)
        {              
            global $wpdb;
            $sql = "update wp_raymand_goods_groups set parent_id = -1 ;"; 
            $wpdb->query($sql);

            global $wpdb;
            $dataArr = json_decode(wp_remote_retrieve_body( $response));
            $insert_query = " INSERT INTO wp_raymand_goods_groups (parent_id, code, title, created, raymand_group_id, store_group_id,level) VALUES ";
            $query = Substr($dataArr->goodsGroups,0,-1);
            $sql = $insert_query . $query;
            $wpdb->query($sql);

            global $wpdb;
            $sql = "delete from wp_raymand_goods_groups where parent_id = -1 ;"; 

            $wpdb->query($sql);

            global $wpdb;
            $sql = " Update wp_raymand_goods_groups as g inner join 
                        ( SELECT    g.id,g.parent_id,g1.id AS new_parent_id,
                                    g1.store_group_id AS parent_store_id FROM wp_raymand_goods_groups g 
                            LEFT JOIN  wp_raymand_goods_groups g1 ON g.parent_id = g1.raymand_group_id 
                        ) AS g1 on g.id = g1.id 
                     set g.parent_id = g1.new_parent_id" ; 
            $wpdb->query($sql); 
            
            global $wpdb;
            $sql = "update wp_raymand_goods_groups g
                    INNER JOIN (SELECT gd.id,g.store_group_id FROM wp_raymand_goods_groups g
                                    LEFT JOIN wp_raymand_goods_groups gd ON gd.parent_id = g.id
                                ) t ON t.id = g.id SET g.parent_store_id = t.store_group_id"; 
            $wpdb->query($sql);


            $message= "Set GoodsGroup Successfully";
        }
        else 
        {
            $code = 1001;
            $message= $response;
        }

        // $this->get_goods_groups($request);
        // $mySetting = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wp_raymand_goods_groups") );
        return array(
            'code' => $code,
            'message' => $message
        ) ;
    }

    public function set_goods_groups($request = null) 
    {
        
        $message = '';
        $sql = '';
        $data = null;
        $code = 1000;
        try {
        global $wpdb;
        $sql = "update wp_raymand_goods_groups set parent_id = -1 ;"; 
        $wpdb->query($sql);
        try {
            // global $wpdb;
            // // $dataArr = json_decode( $request['goods_groups']);
            // // var_dump($dataArr);
            // $insert_query = " INSERT INTO wp_raymand_goods_groups (parent_id, code, title, created, raymand_group_id, store_group_id,level) VALUES ";
            // $query = Substr($request['goods_groups'],0,-1);
            // $sql = $insert_query . $query;
            // $query = $sql;
            // $wpdb->query($sql);
            $dataArr = json_decode($request['goods_groups']);
            // $sqlMain = $dataArr;
            foreach ($dataArr as $arr) {
                
                $sqlMain = Substr($arr,0,-1);
                global $wpdb;
                $insert_query = "INSERT INTO wp_raymand_goods_groups (parent_id, code, title, created, raymand_group_id, store_group_id,level) VALUES ";
                $query = Substr($arr,0,-1);
                $sqlMain = $insert_query . $query;
                $wpdb->query($sqlMain);  
            } 
            
        }
        catch(Error $e){
            $message1 = 'Error writing to database: '.  $e->getMessage();
        }
        global $wpdb;
        $sql = "delete from wp_raymand_goods_groups where parent_id = -1 ;"; 

        $wpdb->query($sql);

        global $wpdb;
        $sql = " Update wp_raymand_goods_groups as g inner join 
                    ( SELECT    g.id,g.parent_id,g1.id AS new_parent_id,
                                g1.store_group_id AS parent_store_id FROM wp_raymand_goods_groups g 
                        LEFT JOIN  wp_raymand_goods_groups g1 ON g.parent_id = g1.raymand_group_id 
                    ) AS g1 on g.id = g1.id 
                 set g.parent_id = g1.new_parent_id" ; 
        $wpdb->query($sql); 
        
        global $wpdb;
        $sql = "update wp_raymand_goods_groups g
                INNER JOIN (SELECT gd.id,g.store_group_id FROM wp_raymand_goods_groups g
                                LEFT JOIN wp_raymand_goods_groups gd ON gd.parent_id = g.id
                            ) t ON t.id = g.id SET g.parent_store_id = t.store_group_id"; 
        $wpdb->query($sql);


        $message= "Set GoodsGroup Successfully";
        }
        catch(Error $e){
            $message1 = 'Error writing to database: '.  $e->getMessage();
        }
        return array(
            'code' => $code,
            'message' => $message,
            'message1' => $query
        ) ;
    }

    function goods_group_parent($current_goods_group , $goods_groups_arr,$goods_groups)
    {
        $parents = [];
        if ($current_goods_group->parent_id != null) {
            foreach ($goods_groups_arr as $parent) {
                if ($parent->id == $current_goods_group->parent_id ) 
                {
                    if($parent->store_group_id == null)
                    {
                        if ($parent->parent_id != null) 
                        {
                            $list = $this->goods_group_parent($parent,$goods_groups_arr,$goods_groups);
                            $parents = array_merge($parents, $list);
                            
                            $index = array_search($current_goods_group->id, array_column($goods_groups, 'id'));
                            $has_exist = ($index !== false);
                            if(!$has_exist) $parents[] = $current_goods_group;
                        } 
                        else 
                        {
                            $index = array_search($parent->id, array_column($goods_groups, 'id'));
                            $has_exist = ($index !== false);
                            if(!$has_exist) $parents[] = $parent;

                            $index = array_search($current_goods_group->id, array_column($goods_groups, 'id'));
                            $has_exist = ($index !== false);
                            if(!$has_exist) $parents[] = $current_goods_group;
                        }
                    }
                    else
                    {
                        $index = array_search($current_goods_group->id, array_column($goods_groups, 'id'));
                        $has_exist = ($index !== false);
                        if(!$has_exist) $parents[] = $current_goods_group;
                    }
                }
            }
        } 
        else 
        {
            $index = array_search($current_goods_group->id, array_column($goods_groups, 'id'));
            $has_exist = ($index !== false);
            if(!$has_exist) $parents[] = $current_goods_group;
        }
        return $parents;
    }

    function get_goods_group_by_level($level , $goods_groups_arr)
    {
        $result = [];
        foreach ($goods_groups_arr as $key=> $value) {
            if($value->level == $level)
            {
                $result[] = array(
                    "name" => $value->title,
                    "parent" => $value->parent_store_id
                );
            }
        }
        return $result;
    }

    function send_to_store ($request , &$body, $level) 
    {
        $level_body = $this->get_goods_group_by_level($level,$body);
        //var_dump( $level_body);
        if(count($level_body) > 0)
        {
            $args = array(
                'method' => 'POST',
                'timeout' => 30,
                'headers' => array(
                    'Authorization' => $request->get_header('authorization')
                ),
                'body' => array('create' => $level_body)
            );
            $response = wp_remote_post( site_url() . '/wp-json/wc/v3/products/categories/batch',$args );
            // var_dump( $request->get_header('authorization'));
            if(wp_remote_retrieve_response_code($response ) == 201 || wp_remote_retrieve_response_code($response ) == 200)
            {
                $categories = json_decode($response['body']);
                foreach ($categories->create as $category)
                {
                    if($category->id > 0)
                    {
                        global $wpdb;
                        $update_query = "update wp_raymand_goods_groups set store_group_id = " . $category->id;
                        $sql = $update_query . " where title = '". $category->name ."'";
                        $wpdb->query($sql);

                        global $wpdb;
                        $update_query = "update wp_raymand_goods_groups g
                                         INNER JOIN
                                         (SELECT gd.* FROM wp_raymand_goods_groups g
                                         LEFT JOIN wp_raymand_goods_groups gd ON gd.parent_id = g.id
                                         WHERE g.title = '". $category->name ."') t
                                         ON t.id = g.id SET g.parent_store_id = " . $category->id;
                        $wpdb->query($update_query);
                        
                        foreach ($body as $key => $value) {
                            if($value->title == $category->name)
                            {
                                $body[$key]->store_group_id = $category->id;
                            
                                foreach ($body as $index => $item) {
                                    if($value->id == $item->parent_id)
                                    {
                                        $body[$index]->parent_store_id = $body[$key]->store_group_id;
                                    }
                                }
                                // $index = array_search($value->id, array_column($body, 'parent_id'));
                                // if($index > 0)
                                //     $body[$index]->parent_store_id = $body[$key]->store_group_id;
                            }
                        }
                    }
                }
            }
        }
    }

    public function goods_group_send_to_store_batch($request = null)
    {
        $message = 'Send GoodsGroup Successfully';
        $data = null;
        $code = 1000;
        $body = [];
        global $wpdb;
		$goods_groups_db = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wp_raymand_goods_groups") );
        foreach ($request['create'] as $category)
        {
            $item = json_decode(json_encode($category), FALSE);
            $parents = $this->goods_group_parent($item ,$goods_groups_db,$body);
            $body = array_merge($body,$parents);
        }
        foreach ([5380,5381,5382,5383,5384,5385,5386,5387,5388,5389] as  $value) {
            $this->send_to_store($request,$body,$value);
        }
        $headers = getallheaders();
        return array(
            'code' => $code,
            'data' =>  $body,
            'message' => $message
        ) ;
    }

}