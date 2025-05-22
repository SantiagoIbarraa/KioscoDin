<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KiosQuiero - Reviews</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

    <main class="main-content">
        <h2>Reseñas de Clientes</h2>
        
        <div class="reviews-container">

        </div>

        <div class="review-details" id="reviewDetails">
            <div class="details-header">
                <h2>DETALLES DE LA RESEÑA</h2>
                <button class="close-details" id="closeReviewDetails">&times;</button>
            </div>
            <div class="details-content">
                <div class="review-info">
                    <h3>Cliente: <span class="customer-name"></span></h3>
                    <p>Fecha: <span class="review-date"></span></p>
                    <div class="rating">
                        <span class="stars"></span>
                    </div>
                </div>
                <div class="review-text">
                    <h4>Comentario</h4>
                    <p class="review-comment"></p>
                </div>
                <div class="review-reply">
                    <h4>Respuesta</h4>
                    <p class="reply-text"></p>
                </div>
                <div class="action-buttons">
                    <button class="btn-reply" id="btnReply">Responder</button>
                </div>
            </div>
        </div>

        <div class="reply-modal" id="replyModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Responder Reseña</h2>
                    <button class="close-modal" id="closeReplyModal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="replyForm">
                        <div class="form-group">
                            <label for="replyText">Tu Respuesta</label>
                            <textarea id="replyText" rows="4" required></textarea>
                        </div>
                        <div class="modal-buttons">
                            <button type="submit" class="btn-confirm">Enviar</button>
                            <button type="button" class="btn-back" id="cancelReply">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="reviews.js"></script>
</body>
</html>
