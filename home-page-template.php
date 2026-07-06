<?php /* Template Name: home page */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
if(is_supplier()){
    wp_redirect(get_site_url()."/archive/?subject=collection");
}
if(is_agent()) {
    $agent_id = get_id_by_user();
}
?>

<?php get_header();
?>
<section class="page flex-display direction-column">
    <?php if(is_manager() && 1==2){?>
    <div class="part-30">
        <div class="font-30 bold margin-bottom-30">פעולות מהירות</div>
        <div class="grid-display cols-4 margin-bottom-20">
            <?php $actions_list = array(
                array("text"=>"הזמנה חדשה","type"=>"single","subject"=>"orders","action"=>"new"),
                array("text"=>"ספק חדש","type"=>"single","subject"=>"suppliers","action"=>"new"),
                array("text"=>"מוצר חדש","type"=>"single","subject"=>"products","action"=>"new"),
                array("text"=>"לקוח חדש","type"=>"single","subject"=>"clients","action"=>"new"),
                array("text"=>"קטלוג","type"=>"archive","subject"=>"products"),
            );
            foreach ($actions_list as $act){
                //write_log("gg ". json_encode($action));
                ?>
                <a href="<?php echo $act["type"].'?subject='.$act["subject"].(isset($act["action"]) ? '&action='.$act["action"]:'') ?>" class="quick-action flex-display align-center border-dark-gray pointer not-link">
                    <?php echo get_svg($act["subject"],(isset($act["action"]) ? $act["action"] :null),false); ?>
                    <div><?php echo $act["text"] ?></div>
                </a>
            <?php } ?>
        </div>
    </div>
    <?php }?>
    <div class=" part-30 flex-display space-between">
        <div class="part-49">
            <div class="flex-display space-between">
                <div class="font-20 bold">חובות פתוחים</div>
                <a class="not-link font-15 dark-green" href="/archive/?subject=collection">לפירוט המלא -></a>
            </div>
            <div class="graphs-charts quick-action border-dark-gray">
                <div class="flex-display direction-column font-18">
                    <?php
                    $filters = array(array("filter_field" => "payment_date","filter_type"=>"null"));
                    $filters[] = array("filter_field" => "test_collection.doc_type","filter_value"=>"1");
                    if(is_agent()){
                        $filters[] = array("filter_table"=>"clients", "filter_field" => "agent_id", "filter_value" => $agent_id);
                    }
                    $result = get_data_table("collection",$filters);

                    $agents = array();
                    foreach ($result as $row){
                        if(empty($row->agents_id))continue;
                        //write_log("row ".json_encode($row));
                        if(!isset($agents[$row->agents_id])){
                            $agents[$row->agents_id] = 0;
                        }
                        $agents[$row->agents_id]+=$row->obligation;
                    }
                        foreach ($agents as $key=>$total){
                            $filters = array(array("filter_field" => "id","filter_value"=>$key));
                            $agent = get_data_table("agents",$filters)[0];
                            ?>
                            <div class="flex-display "><span class="part-30"><?php echo $agent->name ?>:</span><span class="bold gold" ><?php echo "₪".number_format($total) ?> </span> </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="part-49">
            <div class="flex-display space-between">
                <div class="font-20 bold">משימות פתוחות</div>
                <a class="not-link font-15 dark-green" href="/archive/?subject=tasks">לפירוט המלא -></a>
                <!--  צריך להביא פה קישור לעמוד משימות ויראה רק משימות פתוחות -->
            </div>
            <div class="graphs-charts quick-action border-dark-gray">
                <?php
                $filters = array();
                if(is_agent()){
                    $filters[] = array("filter_field" => "id", "filter_value" => $agent_id);
                }
                $agents = get_data_table("agents",$filters);
                $filters = array(array("filter_field" => "status_id", "filter_value" =>1,"filter_type"=>"!="));
                if(is_agent()){
                    $filters[] = array("filter_field" => "test_tasks.agent_id", "filter_value" => $agent_id );                }

                $result = get_data_table("tasks",$filters);
                foreach ($result as $task) {
                    if (empty($task->agent_id)) continue;
                    $agent_id = $task->agent_id;
                    $index = array_search(
                        $agent_id,
                        array_map(fn($a) => $a->id, $agents)
                    );

                    if (!isset($agents[$index]->in_treatment)) {
                        $agents[$index]->in_treatment = 0;
                        $agents[$index]->not_yet_treated = 0;
                    }

                    if($task->status_id == 2){
                        $agents[$index]->in_treatment++;
                    }
                    else if($task->status_id == 3){
                        $agents[$index]->not_yet_treated++;
                    }
                }

                foreach ($agents as $agent){
                    //write_log("row ".json_encode( $task));
                    ?>
                    <div class="font-15 flex-display ">
                        <span class="bold part-30"><?php echo $agent->name ?></span>
                        <span class="part-30"><?php echo "בטיפול: ".(isset($agent->in_treatment)?$agent->in_treatment:0) ?></span>
                        <span class="part-30"><?php echo "טרם טופלו: ". (isset($agent->not_yet_treated)?$agent->not_yet_treated:0) ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>

        </div>
    </div>
    <div class="part-40">
        <div class="flex-display space-between">
            <?php
            $previousMonth = date('m', strtotime('-1 month'));
            $previousYear  = date('Y', strtotime('-1 month'));
            $firstDayOfMonth = "01/".$previousMonth."/".$previousYear;
             $from = $previousYear."-".$previousMonth."-01";
            $lastDayOfMonth = date('t', strtotime($firstDayOfMonth))."/".$previousMonth."/".$previousYear;
            $to = $previousYear."-".$previousMonth."-".date('t', strtotime($firstDayOfMonth));

            ?>
            <div><span class="font-20 bold">גרף מכירות  </span><span class="font-17"><?php echo $firstDayOfMonth ." עד ".$lastDayOfMonth ?></span> </div>
            <div class="font-15 dark-green" ></div>
        </div>
        <div class="graphs-charts quick-action border-dark-gray">
            <?php
            $filters = array();
            if(is_agent()){
                $filters[] = array("filter_field" => "id", "filter_value" => $agent_id);
            }

            $agents = get_data_table("agents",$filters);
            //לקבל את ההזמנות בין תאריכים
            $filters = array(array("filter_field" => "order_date","filter_type"=>"between","filter_value"=> array($from,$to)));
            if(is_agent()) {
                $filters[] = array("filter_field" => "user_opens", "filter_value" =>get_current_user_id());
            }
            $result = get_data_table("orders",$filters);
            foreach ($result as $row) {
                if (empty($row->agent_id)) continue;
                $agent_id = $row->agent_id;
                //write_log("order".json_encode($row));
                $index = array_search(
                    $agent_id,
                    array_map(fn($a) => $a->id, $agents)
                );

                if (!isset($agents[$index]->total)) {
                    $agents[$index]->total = 0;
                    $agents[$index]->_target = 0;
                }
                $agents[$index]->total += $row->total;
            }


            $result = get_data_table("agent_target_supplier");
            //write_log("agent_target_supplier ".json_encode($result));

            foreach ($result as $row) {
                $index = array_search(
                    $row->agent_id,
                    array_map(fn($a) => $a->id, $agents)
                );

                //$agents[$index]->_target += $row->target;
            }

            //write_log("orders+agents ".json_encode($agents));


            ?>
            <canvas id="salesChart"></canvas>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const agents = <?= json_encode($agents, JSON_UNESCAPED_UNICODE) ?>;
                var chart = new Chart(document.getElementById('salesChart'), {
                    type: 'bar',
                    data: {
                        labels: agents.map(a => a.name),
                        datasets: [
                            {
                                label: 'מכר',
                                data: agents.map(a => Number(a.total || 0))
                            },
                            {
                                label: 'יעד',
                                data: agents.map(a => Number(a._target || 0))
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            </script>

        </div>
    </div>
</section>
<?php get_footer();?>