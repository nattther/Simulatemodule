<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Moniteur</title>
	<link rel="stylesheet" href="../Test/asset/style.css">

	<script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
	<div class="header">
		<h1>Monitor-Check</h1>
	</div>


	<!-- Le formulaire  -->
	<div class="formulaires-container">
		<div class="formulaire">
			<button onclick="toggleForm('form')">Ajouter un module</button>
			<form id="form" method="post">
				<p>
					<label for="nom">Nom : </label>
					<input name="nom" id="nom" type="text" />
				</p>
				<input type="submit" name="ok" value="Ajouter" />
			</form>
			<?php
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
				$nom = $_POST['nom'];
				$db = mysqli_connect('localhost', 'root', '', 'monitor');
				if (mysqli_connect_errno()) {
					echo "Erreur de connexion à la base de données : " . mysqli_connect_error();
					exit();
				}
				if ($nom == NULL) {
					echo 'Veuillez reéssayer';
				} else {
					$request = mysqli_prepare($db, "CREATE TABLE $nom (
					ID INT PRIMARY KEY  AUTO_INCREMENT,
					Date DATETIME,
					Puissance FLOAT,
					température FLOAT
				)");
					mysqli_stmt_execute($request);
					header('Location: index.php');
				}
				mysqli_close($db);
			}
			?>
		</div>
		<div class="formulaire">
			<button onclick="toggleForm('formDelete')">supprimer un module</button>
			<form id="formDelete" method="post">
				<p>
					<label for="nom">Nom : </label>
					<input name="nomDelete" id="nomDelete" type="text" />
				</p>
				<input type="submit" name="delete" value="Ajouter" />
			</form>
			<?php
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
				$nom = $_POST['nomDelete'];
				$db = mysqli_connect('localhost', 'root', '', 'monitor');
				if (mysqli_connect_errno()) {
					echo "Erreur de connexion à la base de données : " . mysqli_connect_error();
					exit();
				}
				if ($nom == NULL) {
					echo 'Veuillez reéssayer';
				} else {
					$request = mysqli_prepare($db, "DROP TABLE $nom;
				");
					mysqli_stmt_execute($request);
					header('Location: index.php');
				}
				mysqli_close($db);
			}
			?>
		</div>
	</div>

	<!-- Simulation des modules  -->
	<div>
		<?php
		$db = mysqli_connect('localhost', 'root', '', 'monitor');
		if (mysqli_connect_errno()) {
			echo "Erreur de connexion à la base de données : " . mysqli_connect_error();
			exit();
		}

		$result = mysqli_query($db, "SHOW TABLES");

		// Création des bars détat par modules dans la BDD
		$tables = array();
		$i = 0;
		while ($row = mysqli_fetch_array($result)) {
			$tables[] = $row[0];
			$moduleNameId = 'module-name-' . $i;
			$temperatureId = 'temperature-' . $i;
			$puissanceId = 'puissance-' . $i;
			$dateId = 'date-' . $i;
			$fonctionnementId = 'fonctionnement-' . $i;
			$graphButtonId = 'graph-button-' . $i;
			$chartId = 'temperature-chart-' . $i;
			$i++;
		?>
			<div>
				<div class="bar">
					<div class="item">
						<span>Nom du module :</span>
						<span id="<?php echo $moduleNameId; ?>"></span>
					</div>
					<div class="item">
						<span>Température :</span>
						<span id="<?php echo $temperatureId; ?>"></span>
					</div>
					<div class="item">
						<span>Puissance :</span>
						<span id="<?php echo $puissanceId; ?>"></span>
					</div>
					<div class="item">
						<span>Date :</span>
						<span id="<?php echo $dateId; ?>"></span>
					</div>
					<div class="item">
						<span>Fonctionnement :</span>
						<span id="<?php echo $fonctionnementId; ?>"></span>
					</div>

				</div>
				<div class="graph-contenaire" style="height: 700px; width: 1000px;">
					<canvas id="<?php echo $chartId; ?>" class="graph hidden"></canvas>
				</div>

			</div>
		<?php
		}
		mysqli_close($db);
		?>



		<script>
			var tables = <?php echo json_encode($tables); ?>;

			function simulateModule(moduleId) {
				var xhttp = new XMLHttpRequest();
				xhttp.open("POST", "asset/simulateModule.php", true);
				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp.send("moduleId=" + moduleId);
			}

			setInterval(function() {
				var i;
				for (i = 0; i < tables.length; i++) {
					simulateModule(tables[i]);
				}
			}, 5000);
		</script>

	</div>



	<!-- Le graphique -->
	<script>
		for (i = 0; i < tables.length; i++) {
			var temperatureArrays = [];

			for (i = 0; i < tables.length; i++) {
				var temperatureArray = [];
				temperatureArrays.push(temperatureArray);
			}
		}
		var chart = [];
		var x = 0;
		var compteurseconde = []


		function fetchData(moduleId, i) {
			$.ajax({
				url: 'asset/fetchData.php',
				type: 'POST',
				data: {
					moduleId: moduleId
				},
				dataType: 'json',
				success: function(data) {
					$('#module-name-' + i).text(moduleId);
					$('#temperature-' + i).text(data.température);
					$('#puissance-' + i).text(data.Puissance);
					$('#date-' + i).text(data.Date);
					temperatureArrays[i].push(data.température);
					if (data.température === '0') {
						showNotification("Le module " + $('#module-name-' + i).text() + " est tombé en panne !");

						$('#fonctionnement-' + i).text('En panne');
					} else {
						$('#fonctionnement-' + i).text('En marche');
					}
					if (chart[i]) {
						chart[i].destroy();
					}
					compteurseconde.push(x);
					x = x + 5;

					// Initialiser les données du nouveau graphique
					var ctx = document.getElementById('temperature-chart-' + i).getContext('2d');
					chart[i] = new Chart(ctx, {
						type: 'line',
						data: {
							labels: compteurseconde, 
							datasets: [{
								label: 'Température',
								data: temperatureArrays[i], 
								backgroundColor: 'rgba(0, 123, 255, 0.5)',
								borderColor: 'rgba(0, 123, 255, 1)',
								borderWidth: 1,


							}]
						},
						options: {
							responsive: true,

							scales: {
								y: {
									beginAtZero: true
								}
							},
							width: 1400, 
							height: 900 
						}
					});


				},
				error: function(xhr, status, error) {
					console.log(error);
				}
			});
		}
		//Tourne toutes les 5 secondes pour actualiser les données
		$(document).ready(function() {
			var moduleId = <?php echo json_encode($tables); ?>;
			var i;
			for (i = 0; i < tables.length; i++) {
				fetchData(moduleId[i], i);
			}
			setInterval(function() {
				var y;
				for (y = 0; y < tables.length; y++) {
					fetchData(moduleId[y], y);
				}
			}, 5000);
		});
	</script>







	<script src="../Test/asset/script.js"></script>




</body>

</html>