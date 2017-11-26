
        <h2 class="content__main-heading">Список задач</h2>

        <form class="search-form" action="index.html" method="post">
          <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">

          <input class="search-form__submit" type="submit" name="" value="Искать">
        </form>

        <div class="tasks-controls">
          <nav class="tasks-switch">
            <a href="/" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
            <a href="/" class="tasks-switch__item">Повестка дня</a>
            <a href="/" class="tasks-switch__item">Завтра</a>
            <a href="/" class="tasks-switch__item">Просроченные</a>
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
		        			if ($val['done'] == 'Да') print('task--completed');
        		?>">
        		  <td class="task__select">
        		    <label class="checkbox task__checkbox">
        		      <input class="checkbox__input visually-hidden" type="checkbox" >
        		      <a href="/"><span class="checkbox__text">

                    <?= $val['task'] ?>

                    </span></a>
        		    </label>
        		  </td>

        		  <td class="task__file">
                
        		  </td>

        		  <td class="task__date"><?= $val['dateDeadline'] ?></td>
        		</tr>

        	<?php endforeach; ?>

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
      