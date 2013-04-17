using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Data.Entity;
using System.Linq;
using System.Web;

namespace everything.Models
{
    public class Movie
    {
        public int ID { get; set; }

        [Required(ErrorMessage = "Title is required")]
        public string Title { get; set; }

        [Required(ErrorMessage = "Date is required")]
        [DisplayFormat(DataFormatString = "{0:d}")]
        public DateTime ReleaseDate { get; set; }

        [Required(ErrorMessage = "Genre must be specified")]
        public string Genre { get; set; }

        [Required(ErrorMessage = "Price Required")]
        [Range(1, 100, ErrorMessage = "Price must be between $1 and $100")]
        //[DisplayFormat(DataFormatString = "{0:c}")]
        public decimal Price { get; set; }
    }

    public class MovieDBContext : DbContext
    {
        public DbSet<Movie> Movies { get; set; }
    }
}