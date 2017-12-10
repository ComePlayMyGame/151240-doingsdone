
        <h2 class="content__main-heading">Список задач</h2>

        <form class="search-form" action="index.html" method="post">
          <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">

          <input class="search-form__submit" type="submit" name="" value="Искать">
        </form>

        <div class="tasks-controls">
          <nav class="tasks-switch">
            <a href="/index.php" class="tasks-switch__item <?php if (!isset($_GET['today']) && !isset($_GET['tomorrow']) && !isset($_GET['overdue'])) print('tasks-switch__item--active') ?>">Все задачи</a>
            <a href="/index.php?today" class="tasks-switch__item <?php if (isset($_GET['today'])) print('tasks-switch__item--active') ?>">Повестка дня</a>
            <a href="/index.php?tomorrow" class="tasks-switch__item <?php if (isset($_GET['tomorrow'])) print('tasks-switch__item--active') ?>">Завтра</a>
            <a href="/index.php?overdue" class="tasks-switch__item <?php if (isset($_GET['overdue'])) print('tasks-switch__item--active') ?>">Просроченные</a>
          </nav>

          <label class="checkbox">

            <a href="index.php?show_completed=<?=$show_completed?>">
              <!--добавить сюда аттрибут "checked", если переменная $show_complete_tasks равна единице-->
              <input class="checkbox__input visually-hidden" type="checkbox" <?=$checked?>>
              <span class="checkbox__text">Показывать выполненные</span>
            </a>

          </label>
        </div>

        <table class="tasks">
        	<?php foreach ($tasks as $k => $val): ?>

        		<tr class="tasks__item task 
        		<?php 
		        			if ($val['done'] == 1) print('task--completed');
        		?>">
        		  <td class="task__select">
        		    <label class="checkbox task__checkbox">
        		      <input class="checkbox__input visually-hidden" type="checkbox" >
        		      <a href="/index.php?done=<?= $val['id'] ?>"><span class="checkbox__text">

                    <?= htmlspecialchars($val['task']) ?>

                    </span></a>
        		    </label>
        		  </td>

        		  <td class="task__file">
                <a href="/<?= $val['file'] ?>"><?php if (isset($val['file'])) print($val['file']) ?> </a>
        		  </td>

        		  <td class="task__date"><?= $val['dateDeadline'] ?></td>
        		</tr>

        	<?php endforeach ?>

	<!-- Добавьте класс task--important, если до выполнения задачи меньше дня-->
         <!--  <tr class="tasks__item task <?php if ($days_until_deadline < 1) print("task--important") ?>">
            <td class="task__select">
              <label class="checkbox task__checkbox">
                <input class="checkbox__input visually-hidden" type="checkbox" >
                <a href="/"><span class="checkbox__text">Выполнить домашнее задание</span></a>
              </label>
            </td>

            <td class="task__file">
            </td>

            <td class="task__date"><?= $date_deadline ?></td>
          </tr> -->

          <!--показывать следующий тег <tr/>, если переменная равна единице-->
<!--           <?php if ($show_complete_tasks == 1): ?>
          	<tr class="tasks__item task task--completed">
          	  <td class="task__select">
          	    <label class="checkbox task__checkbox">
          	      <input class="checkbox__input visually-hidden" type="checkbox" checked>
          	      <a href="/"><span class="checkbox__text">Сделать главную страницу Дела в порядке</span></a>
          	    </label>

          	  </td>

          	  <td class="task__file">
          	    <a class="download-link" href="#">Home.psd</a>
          	  </td>

          	  <td class="task__date"></td>
          	</tr>
          <?php endif ?> -->

        </table>
      