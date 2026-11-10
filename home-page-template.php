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
                <div class="grid-display cols-4">
                    <?php
                    $filters = array(array("filter_field" => "payment_date","filter_type"=>"null"));
                    $filters[] = array("filter_field" => "doc_type","filter_value"=>"1");
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
                            <div class="quick-action background-light-gray">
                                <div class="bold gold font-20 margin-bottom-5"><?php echo "₪".number_format($total) ?> </div>
                                <div class="font-15"><?php echo $agent->name ?></div>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="part-49">
            <div class="flex-display space-between">
                <div class="font-20 bold">משימות</div>
                <a class="not-link font-15 dark-green" href="/archive/?subject=tasks">לפירוט המלא -></a>
            </div>
            <div class="graphs-charts quick-action border-dark-gray<?= is_agent() ? " agent-tasks-card" : "" ?>">
                <?php
                //סוכן: סיכום כל המשימות שלו - עוגה לפי מצב (בוצע/בטיפול/טרם טופל), כרטיסיות מספרים ושורות התראה
                if (is_agent()) {
                    global $wpdb;
                    $stats = $wpdb->get_row($wpdb->prepare(
                        "SELECT COUNT(*) AS total,
                                SUM(status_id = 1) AS done,
                                SUM(status_id = 2) AS in_treatment,
                                SUM(status_id = 3) AS not_yet_treated,
                                SUM(status_id != 1 AND DATE(target_date) < CURDATE()) AS overdue,
                                SUM(status_id != 1 AND DATE(target_date) = CURDATE()) AS due_today
                         FROM {$wpdb->prefix}tasks WHERE agent_id = %d", intval(get_id_by_user())));
                    $total = intval($stats->total ?? 0);
                    $done = intval($stats->done ?? 0);
                    $in_treatment = intval($stats->in_treatment ?? 0);
                    $not_yet_treated = intval($stats->not_yet_treated ?? 0);
                    $overdue = intval($stats->overdue ?? 0);
                    $due_today = intval($stats->due_today ?? 0);
                    $done_percent = $total > 0 ? round($done * 100 / $total) : 0;
                    if ($total == 0) { ?>
                        <div class="font-15 agent-tasks-empty">עדיין אין לך משימות</div>
                    <?php } else { ?>
                        <div class="agent-tasks-summary">
                            <div class="agent-tasks-chart">
                                <canvas id="agentTasksChart"></canvas>
                                <div class="agent-tasks-chart-center">
                                    <div class="bold font-20"><?= $total ?></div>
                                    <div class="agent-tasks-small">משימות</div>
                                    <div class="agent-tasks-small bold dark-green"><?= $done_percent ?>% בוצעו</div>
                                </div>
                            </div>
                            <div class="agent-tasks-side">
                                <div class="agent-tasks-tiles">
                                    <div class="agent-tasks-tile total"><span class="bold font-20"><?= $total ?></span><span class="font-15">קיבלת</span></div>
                                    <div class="agent-tasks-tile done"><span class="bold font-20"><?= $done ?></span><span class="font-15">בוצעו</span></div>
                                    <div class="agent-tasks-tile in-treatment"><span class="bold font-20"><?= $in_treatment ?></span><span class="font-15">בטיפול</span></div>
                                    <div class="agent-tasks-tile not-yet-treated"><span class="bold font-20"><?= $not_yet_treated ?></span><span class="font-15">טרם טופלו</span></div>
                                </div>
                                <?php if ($overdue > 0) { ?>
                                    <div class="agent-tasks-alert overdue font-15">⚠ <?= $overdue == 1 ? "למשימה אחת" : "ל-" . $overdue . " משימות" ?> עבר תאריך היעד</div>
                                <?php } ?>
                                <?php if ($due_today > 0) { ?>
                                    <div class="agent-tasks-alert today font-15">● <?= $due_today == 1 ? "למשימה אחת" : "ל-" . $due_today . " משימות" ?> תאריך היעד היום</div>
                                <?php } ?>
                            </div>
                        </div>
                        <script>
                            //Chart.js נטען בהמשך העמוד (גרף המכירות) - מציירים אחרי שהעמוד נטען
                            document.addEventListener('DOMContentLoaded', function () {
                                if (typeof Chart === 'undefined') return;
                                new Chart(document.getElementById('agentTasksChart'), {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['בוצעו', 'בטיפול', 'טרם טופלו'],
                                        datasets: [{
                                            data: [<?= $done ?>, <?= $in_treatment ?>, <?= $not_yet_treated ?>],
                                            backgroundColor: ['#1A7870', '#ce9c3d', '#f8d9a3'],
                                            borderWidth: 2,
                                            borderColor: '#ffffff'
                                        }]
                                    },
                                    options: {
                                        cutout: '72%',
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {legend: {display: false}}
                                    }
                                });
                            });
                        </script>
                    <?php }
                } else {
                    //מנהל: לכל סוכן - כמה משימות קיבל, כמה בוצעו/בטיפול/טרם טופלו, כמה באיחור ואחוז ביצוע. סוכן בלי משימות לא מוצג
                    global $wpdb;
                    $agents_stats = $wpdb->get_results(
                        "SELECT a.id, a.name,
                                COUNT(t.id) AS total,
                                SUM(t.status_id = 1) AS done,
                                SUM(t.status_id = 2) AS in_treatment,
                                SUM(t.status_id = 3) AS not_yet_treated,
                                SUM(t.status_id != 1 AND DATE(t.target_date) < CURDATE()) AS overdue
                         FROM {$wpdb->prefix}agents a
                         INNER JOIN {$wpdb->prefix}tasks t ON t.agent_id = a.id
                         GROUP BY a.id, a.name
                         ORDER BY total DESC, a.name");
                    $columns = array("total" => "קיבל", "done" => "בוצעו", "in_treatment" => "בטיפול", "not_yet_treated" => "טרם טופלו", "overdue" => "באיחור");
                    $sums = array_fill_keys(array_keys($columns), 0);
                    if (empty($agents_stats)) { ?>
                        <div class="font-15 agent-tasks-empty">עדיין אין משימות לסוכנים</div>
                    <?php } else { ?>
                        <table class="agents-tasks-table font-15">
                            <thead>
                            <tr>
                                <th>סוכן</th>
                                <?php foreach ($columns as $key => $label) { ?><th class="num <?= $key ?>"><?= $label ?></th><?php } ?>
                                <th class="progress-col">ביצוע</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($agents_stats as $row) {
                                foreach ($columns as $key => $label) {
                                    $row->$key = intval($row->$key);
                                    $sums[$key] += $row->$key;
                                }
                                $percent = $row->total > 0 ? round($row->done * 100 / $row->total) : 0; ?>
                                <tr>
                                    <td class="bold"><?= esc_html($row->name) ?></td>
                                    <?php foreach ($columns as $key => $label) {
                                        $is_zero = $row->$key == 0 && $key != "total"; ?>
                                        <td class="num <?= $key ?><?= $is_zero ? " zero" : "" ?>"><?= $is_zero ? "–" : $row->$key ?></td>
                                    <?php } ?>
                                    <td class="progress-col">
                                        <div class="tasks-progress" title="<?= $row->done . " מתוך " . $row->total ?>"><div class="tasks-progress-fill" style="width: <?= $percent ?>%"></div></div>
                                        <span class="tasks-progress-text"><?= $percent ?>%</span>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                            <?php if (count($agents_stats) > 1) {
                                $sum_percent = $sums["total"] > 0 ? round($sums["done"] * 100 / $sums["total"]) : 0; ?>
                                <tfoot>
                                <tr>
                                    <td class="bold">סה"כ</td>
                                    <?php foreach ($columns as $key => $label) { ?><td class="num <?= $key ?>"><?= $sums[$key] ?></td><?php } ?>
                                    <td class="progress-col">
                                        <div class="tasks-progress"><div class="tasks-progress-fill" style="width: <?= $sum_percent ?>%"></div></div>
                                        <span class="tasks-progress-text"><?= $sum_percent ?>%</span>
                                    </td>
                                </tr>
                                </tfoot>
                            <?php } ?>
                        </table>
                    <?php }
                }//מנהל
                ?>
            </div>

        </div>
    </div>
    <div class=" part-30 flex-display space-between">
        <div class="part-100">
            <div class="flex-display space-between">
                <div class="font-20 bold">משימות פתוחות</div>
                <a class="not-link font-15 dark-green" href="/archive/?subject=tasks">לפירוט המלא -></a>
                <!--  צריך להביא פה קישור לעמוד משימות ויראה רק משימות פתוחות -->
            </div>
            <div class="graphs-charts quick-action border-dark-gray">
                <?php
                $filters = array();
/*                if(is_agent()){
                    $filters[] = array("filter_field" => "id", "filter_value" => $agent_id);
                }*/
                //$agents = get_data_table("agents",$filters);
                $filters = array(array("filter_field" => "status_id", "filter_value" =>1,"filter_type"=>"!="));
                if(is_agent()){
                    $filters[] = array("filter_field" => "agent_id", "filter_table" => "tasks", "filter_value" => $agent_id );
                }

                $result = get_data_table("tasks",$filters,array("open_date" => "desc"));

                //שורת משימה: מצב, (סוכן - רק למנהל), לקוח עם נקודת חשיבות, נושא, פירוט ותאריך יעד (עבר - אדום, היום - כתום)
                $statuses = array(2 => array("label" => "בטיפול", "class" => "in-treatment"), 3 => array("label" => "טרם טופל", "class" => "not-yet-treated"));
                $importance = array(1 => "גבוהה", 2 => "בינונית", 3 => "נמוכה");
                $today = date('Y-m-d');
                $grid_class = is_agent() ? "open-tasks-grid" : "open-tasks-grid with-agent";
                if (empty($result)) { ?>
                    <div class="open-tasks-empty font-15">אין משימות פתוחות</div>
                <?php } else { ?>
                    <div class="<?= $grid_class ?> open-tasks-head">
                        <span>מצב</span>
                        <?php if (!is_agent()) { ?><span>סוכן</span><?php } ?>
                        <span>לקוח</span><span>נושא</span><span>פירוט</span><span>תאריך יעד</span><span>תזכורת</span>
                    </div>
                <?php }
                foreach ($result as $task) {
                    $status = $statuses[intval($task->status_id)] ?? array("label" => "", "class" => "");
                    $target = !empty($task->target_date) ? date('Y-m-d', strtotime($task->target_date)) : "";
                    $date_class = $target === "" ? "none" : ($target < $today ? "overdue" : ($target == $today ? "today" : ""));
                    $date_text = $target === "" ? "—" : ($target == $today ? "היום" : date('d/m/Y', strtotime($target)));
                    //תזכורת: פעמון ותאריך; תזכורת שהגיעה (היום או לפני) - מודגשת
                    $reminder = !empty($task->sending_reminder) ? date('Y-m-d', strtotime($task->sending_reminder)) : "";
                    $reminder_class = $reminder === "" ? "none" : ($reminder <= $today ? "arrived" : "");
                    $reminder_text = $reminder === "" ? "—" : ($reminder == $today ? "היום" : date('d/m/Y', strtotime($reminder)));
                    ?>
                    <a href="<?= esc_url(get_site_url() . '/single?subject=tasks&action=edit&id=' . intval($task->id)) ?>" class="open-task <?= $grid_class ?> not-link font-15">
                        <span><span class="open-task-status <?= esc_attr($status["class"]) ?>"><?= esc_html($status["label"]) ?></span></span>
                        <?php if (!is_agent()) { ?><span class="bold"><?= esc_html($task->agent_name) ?></span><?php } ?>
                        <span class="bold"><span class="open-task-importance importance-<?= intval($task->importance_id) ?>" title="<?= esc_attr("חשיבות " . ($importance[intval($task->importance_id)] ?? "")) ?>"></span><?= esc_html($task->client_name) ?></span>
                        <span><?= esc_html($task->subject_text) ?></span>
                        <span class="open-task-details" title="<?= esc_attr($task->details) ?>"><?= esc_html($task->details) ?></span>
                        <span><span class="open-task-date <?= $date_class ?>"><?= esc_html($date_text) ?></span></span>
                        <span><span class="open-task-reminder <?= $reminder_class ?>"><?= esc_html($reminder_text) ?></span></span>
                    </a>
                <?php } ?>
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